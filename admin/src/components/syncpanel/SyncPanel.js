import React, { useState } from "react";
import { wp_fetch, wp_fetch_new_tab } from "../../helpers/wpajax";
import Panel from "../Panel";
import SyncPanelForm from "./SyncPanelForm";
import SyncPanelReview from "./SyncPanelReview";
import SyncPanelSubmitted from "./SyncPanelSubmitted";

let abortController = new AbortController();

export default function SyncPanel({
	title,
	open,
	selection,
	type,
	children,
	onClose = () => { },
	onReset = () => { },
}) {
	const [step, setStep] = useState(1);
	const [syncStatus, setSyncStatus] = useState(false);

	const cancelHandler = () => {
		abortController.abort()

		setSyncStatus((prev) => {
			return {
				...prev,
				status: 'cancelled',
			}
		});

		window.toast?.create("File transfer cancelled!", 'block')
	}

	const resetHandler = () => {
		setSyncStatus(false);
		setStep(1);
		abortController = new AbortController();
		onReset();
	}

	const handleNextStep = () => {
		setStep(step + 1);
	}

	const handleSync = async () => {
		if (!selection.length) {
			// return
		}

		document.dispatchEvent(new Event('flightdeck/sync-start'));

		setStep(3);
		setSyncStatus({
			status: 'connecting',
			errors: [],
			log: [],
		});

		const resp = await wp_fetch('sync_connection', { selection, type }, {
			signal: abortController.signal
		});

		// Fetch failed/server returned an error
		if (!resp.ok) {
			try {
				const json = await resp.json()
				const messages = json.data.messages.map(m => m.message)

				setSyncStatus((prev) => {
					return {
						...prev,
						status: 'error',
						errors: messages,
					}
				});
			}
			catch (e) {
				console.log(resp);

				setSyncStatus((prev) => {
					return {
						...prev,
						status: 'error',
						errors: [`Could not connect to back-end (${resp.status}).`],
					}
				});
			}

			document.dispatchEvent(new Event('flightdeck/sync-fail'));
			document.dispatchEvent(new Event('flightdeck/sync-done'));
			window.toast?.create("Sync failed!", 'warning')

			return;
		}

		setSyncStatus((prev) => {
			return {
				...prev,
				status: 'sending',
			}
		});

		// Stream the server's ongoing response
		const reader = resp.body.pipeThrough(new TextDecoderStream()).getReader();

		while (true) {
			try {
				const { value, done } = await reader.read();
				if (done) break;

				// Logs are sent as JSON with line breaks.
				const logs = value.split(/\r?\n/).filter(l => l.trim().length > 0).map(JSON.parse);

				setSyncStatus((prev) => {
					return {
						...prev,
						log: [
							...prev.log,
							...logs
						],
					}
				});
			}
			catch (e) {
				console.error(e);
				break;
			}
		}

		setSyncStatus((prev) => {
			return {
				...prev,
				status: 'success',
			}
		});

		window.toast?.create("Sync complete!", 'check_circle')
		document.dispatchEvent(new Event('flightdeck/sync-success'));
		document.dispatchEvent(new Event('flightdeck/sync-done'));
	}

	const handleDownload = async () => {
		window.toast.create("Downloading...", "cloud_download")
		wp_fetch_new_tab('download_backup', { selection, type })
	}

	const renderPanelContent = () => {
		switch (step) {
			case 1:
				return (
					<SyncPanelForm onSubmit={handleNextStep} selectionCount={selection.length}>
						{children}
					</SyncPanelForm>
				)
			case 2:
				return (
					<SyncPanelReview onSync={handleSync} onDownload={handleDownload} onReset={resetHandler} selection={selection} type={type} />
				)
			case 3:
				return (
					<SyncPanelSubmitted syncStatus={syncStatus} onCancel={cancelHandler} onReset={resetHandler} />
				)
		}
	}

	return (
		<Panel title={title} open={open} onClose={onClose}>
			{renderPanelContent()}
		</Panel>
	)
}
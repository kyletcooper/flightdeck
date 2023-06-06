import React, { useEffect, useState } from "react";
import OTPInput from "./OTPInput";
import useAPI from "./hooks/useAPI";
import useAPILoading from "./hooks/useAPILoading";
import Button from "./Button";
import Icon from "./Icon";

export default function AuthCodePreview() {
	const [time, setTime] = useState(Math.floor(Date.now() / 1000));
	const [{ flightdeck_auth_code, flightdeck_auth_code_expires }, setSettings] = useAPI('settings');
	const isLoading = useAPILoading('settings');

	const handleGenerateAuthCode = () => {
		setSettings({
			flightdeck_auth_code: "",
		})
	}

	const formatExpiry = () => {
		if (!flightdeck_auth_code_expires) {
			return;
		}

		if (time > flightdeck_auth_code_expires) {
			return <time className="text-red-700 font-medium underline underline-offset-2 decoration-red-500 decoration-1 decoration-dotted">Expired</time>
		}

		let diff = Math.abs(flightdeck_auth_code_expires - time);

		const hours = Math.floor(diff / (60 * 60));
		diff -= (hours * 60 * 60);

		const mins = Math.floor(diff / 60);
		diff -= (mins * 60);

		const secs = diff;

		return <time className="text-red-700 font-medium underline underline-offset-2 decoration-red-500 decoration-1 decoration-dotted">Expires in {hours}:{mins}:{secs}</time>
	}

	useEffect(() => {
		const timer = setInterval(() => {
			setTime(Math.floor(Date.now() / 1000));
		}, 1000);

		return () => clearInterval(timer);
	}, []);

	return (
		<div className="grid gap-4">
			<div className={"relative " + (isLoading && 'opacity-50')}>
				<OTPInput code={flightdeck_auth_code} readOnly maxLength={5} segmentLength={1} />

				<div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
					{isLoading && <Icon icon={'refresh'} className="text-blue-500" animation="spin" />}
				</div>
			</div>

			<div className="flex items-center justify-between gap-4">
				<Button onClick={handleGenerateAuthCode} disabled={isLoading}>
					<Icon icon="sync_lock" /> Generate Auth Code
				</Button>

				{formatExpiry()}
			</div>
		</div>
	)
}
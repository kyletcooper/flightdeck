import React, { useState } from "react";
import Button from "../Button";
import Chip from "../Chip";
import Collapse from "../Collapse";
import useAPI from "../hooks/useAPI";
import useAPILoading from "../hooks/useAPILoading";
import Icon from "../Icon";
import RulesList from "../RulesList";
import Title from "../Title";
import Switch from "../Switch";

export default function SyncPanelReview({
	selection,
	type,
	onSync,
	onDownload,
	onReset,
	onSchedule,
}) {
	const [isAccepted, setIsAccepted] = useState(false);
	const [{ address, warnings, errors }] = useAPI('connection');
	const isLoading = useAPILoading('connection');

	const hasWarnings = warnings?.some(warning => !warning.success)
	const hasErrors = errors?.some(error => !error.success)

	const handleToggleAccepted = evt => setIsAccepted(evt.target.checked);

	const tableRows = [
		{
			label: "Destination",
			value: address || "Loading...",
		},
		{
			label: "Selection",
			value: selection.length + " Items"
		},
		{
			label: "Sync Type",
			value: type.charAt(0).toUpperCase() + type.toLowerCase().slice(1),
		},
	]

	return (
		<div className="flex flex-col h-full">
			<div className="grow">
				<div className="p-5">
					<Title level={4}>Review your Departure</Title>

					<table className="w-full">
						<tbody>
							{tableRows.map(row => (
								<tr key={row.label}>
									<th className="text-sm font-normal text-gray-900 py-2">
										{row.label}
									</th>

									<td className="text-sm text-right text-gray-500 py-2">
										{row.value}
									</td>
								</tr>
							))}
						</tbody>
					</table>

					<div className="flex items-center gap-2 py-3 px-4 rounded mt-6 bg-blue-50">
						<Icon icon="warning" className="text-blue-500" />
						<Switch className="grow" onChange={handleToggleAccepted} inline>I've read the warnings and wish to continue</Switch>
					</div>
				</div>

				<Collapse title="Connection Status" chip={isLoading ? <Chip color="gray" icon="refresh">Loading...</Chip> : !hasErrors ? <Chip color="green" icon="rss_feed">Connected</Chip> : <Chip color="red" icon="error">Errors found</Chip>}>
					<p className="mb-5">
						You can't begin a sync if any of these checks fail.
					</p>

					<RulesList rules={errors} loading={isLoading} />
				</Collapse>

				<Collapse title="Pre-flight Checks" chip={isLoading ? <Chip color="gray" icon="refresh">Loading...</Chip> : !hasWarnings ? <Chip color="green" icon="check_circle">No warnings</Chip> : <Chip color="orange" icon="warning">Warnings found</Chip>}>
					<p className="mb-5">
						These warnings will not stop the data from being transferred but may cause issues/bugs on your destination site.
					</p>

					<RulesList rules={warnings} loading={isLoading} />
				</Collapse>
			</div>

			<div className="flex items-center justify-between sticky bottom-0 p-5 w-full bg-white border-t border-gray-300">
				<Button onClick={onReset} style='secondary' small>
					<Icon icon="arrow_back" size="18" />
					Go Back
				</Button>

				<Button onClick={onSync} style='primary' disabled={hasErrors || !isAccepted} small moreTop more={[
					{
						icon: "cloud_download",
						label: "Download Transfer",
						onClick: onDownload
					}
				]}>
					<Icon icon="send" size="18" />
					Send Transfer
				</Button>
			</div>
		</div>
	)
}
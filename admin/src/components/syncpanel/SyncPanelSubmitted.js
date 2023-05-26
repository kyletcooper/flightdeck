import React from "react";
import RulesList from "../RulesList";
import SyncStatus from "./SyncStatus";
import Title from "../Title";
import Icon from "../Icon";
import Button from "../Button";
import Collapse from "../Collapse";

export default function SyncPanelSubmitted({
	syncStatus,
	onCancel,
	onReset,
}) {
	// Unique items in the log, sorted by timestamp
	const items = syncStatus.log.sort((a, b) => a.timestamp - b.timestamp).filter((item, index, arr) =>
		(item.type == 'file' || item.type == 'table') &&
		arr.filter((i, ii) => i.name == item.name).at(-1) === item // Checks if this is the last item with this path
	);

	const erroredItems = syncStatus.log.filter(item => item.status === 'failed');

	return (
		<div className="h-full flex flex-col">
			<div className="grow">
				<div className="p-5">
					<SyncStatus status={syncStatus.status} />

					{
						syncStatus.errors.length > 0 &&
						<RulesList rule={syncStatus.error} className="mt-6" />
					}

					{
						erroredItems.length > 0 &&
						<div className="mt-6 rounded-md p-6 bg-gray-50 font-medium">
							{erroredItems.length + (erroredItems.length > 1 ? ' transfers' : ' transfer')} failed.
						</div>
					}
				</div>

				<Collapse title="Status">
					<table className="table-fixed w-full">
						<thead>
							<tr>
								<th className="text-left font-medium">Item</th>

								<th className="w-14 text-right font-medium">
									Status
								</th>
							</tr>
						</thead>
						<tbody>
							{
								items.map(file => (
									<tr key={file.name}>
										<td className="text-left whitespace-nowrap text-ellipsis overflow-hidden">
											<span className={file.status == 'success' || file.status == 'error' ? 'text-gray-900' : 'text-gray-600'}>
												{file.name}
											</span>
										</td>

										<td className="text-right">
											{
												file.status == 'success' ?
													<Icon icon="check_circle" label="Success" className="w-fit ml-auto text-green-500" />
													: file.status == 'failed' ?
														<Icon icon="cancel" label="Error" className="w-fit ml-auto text-red-500" />
														:
														syncStatus.status == 'cancelled' ?
															<Icon icon="block" label="Cancelled" className="w-fit ml-auto text-orange-500" />
															:
															<Icon icon="refresh" label="Syncing" animation="spin" className="w-fit ml-auto text-blue-500" />

											}
										</td>
									</tr>
								))
							}
						</tbody>
					</table>

					{items.length === 0 && (
						<div className="text-gray-500 mt-1 mb-2">
							No items sent
						</div>
					)}
				</Collapse>

				<Collapse title="Log">
					<textarea readOnly={true} value={JSON.stringify(syncStatus)} className="p-3 w-full resize-none h-[50vh] bg-white text-gray-500 text-xs font-mono overflow-y-scroll select-all border border-gray-300 rounded whitespace-normal break-words">
					</textarea>
				</Collapse>
			</div>

			<div className="border-t border-gray-300 bg-white p-5 sticky bottom-0">
				{
					syncStatus.status === 'connecting' || syncStatus.status === 'sending' ?
						<Button onClick={onCancel} small style="destructive">
							<Icon icon="delete" size={18} />
							Cancel
						</Button>
						:
						<Button onClick={onReset} small>
							<Icon icon="refresh" size={18} />
							Sync again
						</Button>
				}
			</div>

		</div >
	)
}
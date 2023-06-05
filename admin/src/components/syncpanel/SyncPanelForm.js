import React from "react";
import Button from "../Button";
import Icon from "../Icon";
import Tooltip from "../Tooltip";

export default function SyncPanelForm({
	children,
	onSubmit = () => { },
	selectionCount = 0
}) {
	return (
		<div className="flex flex-col h-full">
			<div className="grow">
				{children}
			</div>

			<div className="flex items-center justify-between sticky bottom-0 p-5 w-full bg-white border-t border-gray-300 rounded-bl-xl">
				<span className="text-medium">
					{selectionCount} Selected
				</span>

				<Tooltip title="You must select at least 1 item" disable={selectionCount !== 0}>
					<Button onClick={onSubmit} disabled={selectionCount === 0} style='secondary' small>
						<Icon icon="arrow_forward" size="18" />
						Review Transfer
					</Button>
				</Tooltip>
			</div>
		</div>
	)
}
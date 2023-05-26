import React, { useEffect, useState } from "react";
import DataStore from "../../DataStore";
import Icon from "../Icon";

export default function SyncStatus({
	status
}) {
	let [destination, setDestination] = useState("Connecting...");

	useEffect(() => {
		DataStore.settings.subscribe(settings => setDestination(settings.flightdeck_foreign_address))
	}, []);

	// Status can be 'connecting', 'sending', 'success', 'error' or 'cancelled'.

	let bgClass = 'bg-blue-50';
	let textClass = 'text-blue-500';
	let borderClass = 'border-blue-500';
	let dashWidthClass = 'w-1/2';
	let animation = '';
	let icon = 'refresh';

	switch (status) {
		case 'connecting':
			animation = 'spin';
		default:
			break;

		case 'sending':
			dashWidthClass = 'w-full';
			icon = "send";
			break;

		case 'success':
			bgClass = 'bg-emerald-50';
			textClass = 'text-emerald-500';
			borderClass = 'border-emerald-500';
			dashWidthClass = 'w-full';
			icon = "done";
			break;

		case 'error':
			bgClass = 'bg-red-50';
			textClass = 'text-red-500';
			borderClass = 'border-red-500';
			dashWidthClass = 'w-1/2';
			icon = "close";
			break;

		case 'cancelled':
			bgClass = 'bg-orange-50';
			textClass = 'text-orange-500';
			borderClass = 'border-orange-500';
			dashWidthClass = 'w-1/2';
			icon = "block";
			break;
	}

	return (
		<div className="grid grid-cols-3">
			<div className={["p-5 flex flex-col gap-3 items-center rounded text-xs transition-colors ", textClass, bgClass].join(" ")}>
				<Icon icon="language" size="36" />

				<div className="text-ellipsis text-sm max-w-full overflow-hidden whitespace-nowrap">
					{window.flightdeck.home_url}
				</div>
			</div>

			<div className="relative">
				<div className={["absolute top-1/2 left-0 border-b-2 border-dashed transition-colors ", dashWidthClass, borderClass].join(" ")}>
				</div>

				<div className="absolute top-1/2 left-1/2 z-10 -translate-x-1/2 -translate-y-1/2">
					{status === 'sending' &&
						<div className="absolute inset-0 w-full h-full bg-blue-500 rounded-full motion-safe:animate-ping"></div>
					}

					<Icon icon={icon} animation={animation} className={["relative z-10 border-2 transition-colors ", textClass, borderClass, bgClass].join(" ")} />
				</div>

				<div className={"absolute left-0 right-0 bottom-4 text-center text-xs " + textClass}>
					{getStatusMessage(status)}
				</div>
			</div>

			<div className={["p-5 flex flex-col gap-3 items-center rounded text-xs transition-colors ", status == 'connecting' ? 'text-gray-500' : textClass, status == 'connecting' ? 'bg-gray-50' : bgClass].join(" ")}>
				<Icon icon="language" size="36" />

				<div className="text-ellipsis text-sm max-w-full overflow-hidden whitespace-nowrap">
					{destination}
				</div>
			</div>
		</div>
	)
}

const getStatusMessage = (status) => {
	switch (status) {
		case 'connecting':
			return 'Connecting...'

		case 'sending':
			return 'Syncing...'

		case 'success':
			return 'Complete'

		case 'cancelled':
			return 'Cancelled'

		default:
			return 'Error'
	}
}
import React from "react";
import Icon from "./Icon";

export default function ConnectionStatus({
	status
}) {
	switch (status) {
		case 'loading':
			return (
				<div className="flex items-center gap-4 rounded-md p-5 bg-blue-100 text-blue-900">
					<Icon icon='refresh' className="bg--white text-blue-500 motion-safe:animate-spin" />

					<div className="font-medium">
						Testing connection...
					</div>
				</div>
			)

		case 'error':
			return (
				<div className="flex items-center gap-4 rounded-md p-5 bg-red-100 text-red-900">
					<Icon icon='close' className="bg-white text-red-500" />

					<div className="font-medium">
						Connection failed
					</div>
				</div>
			)

		case 'success':
			return (
				<div className="flex items-center gap-4 rounded-md p-5 bg-green-100 text-green-900">
					<Icon icon='done' className="bg-white text-green-500" />

					<div className="font-medium">
						Connection established
					</div>
				</div>
			)
	}
}
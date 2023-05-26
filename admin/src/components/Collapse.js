import React, { useState } from "react";
import Icon from "./Icon";
import Title from "./Title";

export default function Collapse({
	title,
	children,
	chip,
	open = true,
}) {
	return (
		<details className="group border-t last-of-type:border-b border-gray-300" open={open}>
			<summary className="flex items-center gap-4 px-6 py-4 cursor-pointer rounded-md border border-transparent ring-0 transition-all hover:bg-gray-50 focus-visible:outline-none focus-visible:bg-blue-50 focus-visible:ring-4 focus-visible:border-blue-500">
				<Title level={4} className="!mb-0 mr-auto">
					{title}
				</Title>

				{chip}

				<Icon icon="chevron_right" className="text-gray-400 transition-transform group-open:rotate-90" />
			</summary>

			<div className="p-6 pt-4">
				{children}
			</div>
		</details>
	)
}
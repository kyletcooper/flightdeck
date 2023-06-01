import React from "react";
import TablePickerItemIcon from "./TablePickerItemIcon";

export default function TablePickerItem({
	table,
	selected = false,
	onClick = () => { },
	onDoubleClick = () => { },
	className,
}) {
	let classes = [
		"flex items-center gap-5 p-5 w-full rounded-md text-left transition-all border border-transparent ring-0 focus-visible:rounded focus-visible:outline-none focus-visible:border-blue-500 focus-visible:ring-4 focus-visible:ring-blue-200",
		className
	];

	if (selected) {
		classes.push("bg-blue-100 text-blue-500 hover:bg-blue-50 focus-visible:bg-blue-50");
	}
	else {
		classes.push("bg-gray-100 hover:bg-gray-50");
	}

	return (
		<li className="mb-0">
			<button onClick={onClick} onDoubleClick={onDoubleClick} className={classes.join(" ")}>
				<TablePickerItemIcon table={table} selected={selected} />

				<div>
					<h4 className="text-base font-medium">
						{table.name}
					</h4>
					<p className="text-xs opacity-70">
						{table.count} Rows
					</p>
				</div>
			</button>
		</li>
	)
}
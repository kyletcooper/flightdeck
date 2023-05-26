import React from "react"
import Icon from "./Icon"

export default function Chip({
	children,
	icon,
	color = 'green',
}) {
	let iconClasses = [];
	let chipClasses = ["flex items-center gap-1 text-xs rounded-full pl-1 pr-3"];

	const colorSchemes = {
		green: {
			icon: "text-green-600",
			chip: "bg-green-100 text-green-600"
		},
		orange: {
			icon: "text-orange-600",
			chip: "bg-orange-100 text-orange-600"
		},
		yellow: {
			icon: "text-yellow-600",
			chip: "bg-yellow-100 text-yellow-600"
		},
		red: {
			icon: "text-red-600",
			chip: "bg-red-100 text-red-600"
		},
		blue: {
			icon: "text-blue-600",
			chip: "bg-blue-100 text-blue-600"
		},
		gray: {
			icon: "text-gray-600",
			chip: "bg-gray-100 text-gray-600"
		}
	}

	chipClasses.push(colorSchemes[color].chip)
	iconClasses.push(colorSchemes[color].icon)

	return (
		<div className={chipClasses.join(" ")}>
			{icon && <Icon icon={icon} className={iconClasses.join(' ')} size={16} />}
			{children}
		</div>
	)
}
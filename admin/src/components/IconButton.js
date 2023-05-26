import React from "react"
import Icon from "./Icon"

export default function IconButton({
	icon,
	label,
	className,
	onClick,
	size,
	disabled = false,
	style = 'blue',
}) {
	const classes = [
		'p-2 transition-all border border-transparent ring-0 ring-transparent'
	]

	if (disabled) {
		classes.push('opacity-50');
	}
	else {
		switch (style) {
			case 'blue':
				classes.push('group-focus:text-blue-500 hover:text-blue-500 group-focus:ring-4 group-focus:ring-blue-200 group-focus:border-blue-500 group-focus:bg-blue-50 group-hover:bg-blue-50');
				break;

			case 'transparent':
				classes.push('group-focus:text-white hover:text-white group-focus:ring-4 group-focus:ring-white/10 group-focus:border-white/70 group-focus:bg-white/30 group-hover:bg-white/30');
				break;
		}
	}

	return (
		<button disabled={disabled} onClick={onClick} aria-label={label} className={'group focus:outline-none ' + className}>
			<Icon {...{ size, icon }} className={classes.join(" ")} />
		</button>
	)
}
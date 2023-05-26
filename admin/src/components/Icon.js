import React from "react"

export default function Icon({
	icon,
	animation,
	label = '',
	size = 24,
	className = ''
}) {
	let animationClass = 'animate-none';
	switch (animation) {
		case 'spin':
			animationClass = 'animate-spin';
			break;

		case 'ping':
			animationClass = 'animate-ping';
			break;

		case 'pulse':
			animationClass = 'animate-pulse';
			break;

		case 'bounce':
			animationClass = 'animate-bounce';
			break;
	}

	return (
		<div aria-hidden="true" aria-label={label} title={label} className={'icon flex items-center justify-center rounded-full p-1 select-none ' + animationClass + ' ' + className} >
			<span className="material-icons-round" style={{ fontSize: size + "px" }}>
				{icon}
			</span>
		</div >
	)
}
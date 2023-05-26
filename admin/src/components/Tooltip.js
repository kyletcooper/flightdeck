import React, { useState } from "react";

export default function Tooltip({
	title = '',
	disable = false,
	children = null,
	className,
}) {
	const [isActive, setIsActive] = useState(false);
	const [pos, setPos] = useState({ x: 0, y: 0 });

	const positionTooltip = (evt) => {
		setPos({
			x: evt.clientX,
			y: evt.clientY
		})
	}

	const showTooltip = (evt) => {
		positionTooltip(evt);
		setIsActive(true)
	}

	const hideTooltip = () => {
		setIsActive(false)
	}

	return (
		<div className={"w-fit h-fit " + className} onMouseMove={positionTooltip} onMouseOver={showTooltip} onMouseOut={hideTooltip}>
			{children}

			{
				isActive && !disable &&

				<div className="fixed z-50 translate-x-4 translate-y-4 bg-white whitespace-nowrap shadow-md rounded py-2 px-4 text-xs" style={{ top: pos.y, left: pos.x }}>
					{title}
				</div>
			}
		</div>
	)
}
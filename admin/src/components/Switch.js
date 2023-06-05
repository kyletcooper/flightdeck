import React, { useRef, useState } from "react";

export default function Switch({
	className = '',
	onChange,
	onBlur,
	disabled,
	indicator,
	inline = false,
	children,
	innerRef,
}) {
	return (
		<label className={`${inline ? "flex justify-between items-center gap-4" : "flex flex-col gap-2 max-w-fit"} ${className}`}>
			<div className="flex items-center gap-3 h-8">
				<span className={"" + (disabled && "text-gray-400")}>
					{children}
				</span>

				{indicator}
			</div>


			<div className="p-1 w-12 bg-gray-200 rounded-full ring-0 ring-transparent transition-all [&:has(:disabled)]:opacity-40 [&:has(:checked)]:bg-blue-500 hover:opacity-80 focus-within:opacity-80 focus-within:ring-4 focus-within:ring-blue-200">
				<input className="sr-only peer" type="checkbox" {...{ disabled, onChange, onBlur }} ref={innerRef} value="1" />
				<div className="w-4 h-4 rounded-full transition-transform bg-white peer-checked:translate-x-6"></div>
			</div>
		</label>
	)
}
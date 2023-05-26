import React from "react"

export default function Input({
	className,
	type,
	value,
	defaultValue,
	placeholder,
	disabled,
	autoComplete,
	children,
	indicator,
	onChange,
	onEnter,
	onInput,
	onFocus,
	onBlur,
	innerRef,
}) {
	const keydownHandler = (evt) => {
		if (evt.key === 'Enter') {
			typeof onEnter == 'function' && onEnter(evt);
		}
	}

	return (
		<label className={"flex flex-col gap-2 " + className}>
			<span className="text-sm text-gray-600">
				{children}
			</span>

			<div className="flex bg-white border border-gray-300 rounded text-gray-900 ring-0 ring-transparent transition-all hover:border-blue-500 focus-within:ring-4 focus-within:border-blue-500 focus-within:ring-blue-200">
				<input
					className="grow bg-transparent py-2 px-3 border-none shadow-none focus:outline-none"
					{...{ type, value, defaultValue, placeholder, disabled, autoComplete, onChange, onInput, onFocus, onBlur }}
					ref={innerRef}
					onKeyDown={keydownHandler}
				/>

				{indicator}
			</div>
		</label>
	)
}
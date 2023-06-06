import React, { useEffect, useRef, useState } from "react"

export default function OTPInput({
	maxLength = 5,
	segmentLength = 1,
	code = '',
	readOnly = false,
	onChange = () => { },
	className = 'h-16 text-2xl',
}) {
	const [value, setValue] = useState(code);
	const [caret, setCaret] = useState(-1);
	const [hasFocus, setHasFocus] = useState(false);
	const inputRef = useRef();

	const numSegments = Math.ceil(maxLength / segmentLength);
	const caretSegmentIndex = Math.min(Math.ceil((caret + 1) / segmentLength) - 1, numSegments - 1);

	useEffect(() => {
		setValue(code);
	}, [code])

	const handleChange = evt => {
		setValue(evt.target.value);
		onChange(value);
	}

	const handleKeyUp = evt => {
		const input = inputRef.current;

		if (evt.key === 'ArrowLeft' && input.selectionStart > 0) {
			input.selectionStart -= 1;
		}

		if (input.selectionStart === maxLength) {
			input.selectionStart = maxLength - 1;
			input.selectionEnd = maxLength;
		}

		input.selectionEnd = input.selectionStart + 1;

		setCaret(input.selectionStart)
	}

	const handleFocus = evt => {
		setHasFocus(true);
	}

	const handleBlur = evt => {
		setHasFocus(false);
	}

	const setSegmentFocus = segmentIndex => {
		const input = inputRef.current;
		input.focus();

		if (readOnly) {
			input.selectionStart = 0;
			input.selectionEnd = value.length;
			return;
		}

		input.selectionStart = segmentIndex * segmentLength;
		input.selectionEnd = input.selectionStart + 1;
		setCaret(input.selectionStart)
	}

	const getSegmentValue = index => {
		return value.substring(index * segmentLength, (index + 1) * segmentLength);
	}

	const isSegmentHighlighted = index => {
		if (readOnly) {
			return hasFocus;
		}

		return hasFocus && caretSegmentIndex === index;
	}

	return (
		<div className={className}>
			<input className="sr-only" ref={inputRef} value={value} onChange={handleChange} onKeyUp={handleKeyUp} maxLength={maxLength} readOnly={readOnly} onFocus={handleFocus} onBlur={handleBlur} />

			<div className="grid grid-cols-[repeat(auto-fit,minmax(0,1fr))] gap-3 h-full">
				{
					[...Array(numSegments)].map((e, i) => (

						<div className={"h-full flex items-center justify-center border border-gray-300 rounded text-gray-900 ring-0 ring-transparent transition-all " + (isSegmentHighlighted(i) && 'ring-4 !border-blue-500 ring-blue-200')} onClick={() => setSegmentFocus(i)} key={i}>
							{getSegmentValue(i)}
						</div>

					))
				}
			</div>
		</div>
	)
}
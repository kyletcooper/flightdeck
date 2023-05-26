import React, { useEffect, useId, useRef, useState } from "react"
import Icon from "./Icon";

export default function Button({
	onClick,
	style = 'secondary',
	disabled = false,
	small = false,
	className = '',
	href = '',
	target,
	children,
	more,
	moreTop = false
}) {
	const [isDropdownOpen, setIsDropdownOpen] = useState(false);
	const dropdownID = useId();
	const dropdownRef = useRef();

	useEffect(() => {
		const handleClickOffDropdown = (evt) => {
			if (dropdownRef.current && !dropdownRef.current.contains(evt.target)) {
				setIsDropdownOpen(false);
			}
		}

		document.addEventListener("mousedown", handleClickOffDropdown);

		return () => {
			document.removeEventListener("mousedown", handleClickOffDropdown);
		}
	}, []);

	const baseClasses = "flex items-center justify-center w-full font-medium ring-0 transition-all focus:ring-4 focus:outline-none";
	const sizingClasses = small ? "text-sm py-2 px-4 gap-1 [&:has(.icon)]:pl-3" : "py-3 px-6 gap-2 [&:has(.icon)]:pl-4";

	const styles = {
		primary: {
			enabled: "text-white bg-blue-500 hover:bg-blue-600 focus:ring-blue-200",
			disabled: "text-white bg-blue-200 focus:ring-blue-50"
		},
		secondary: {
			enabled: "text-gray-900 bg-gray-200 hover:bg-gray-100 focus:ring-blue-200",
			disabled: "text-gray-900 bg-gray-50 focus:ring-blue-50"
		},
		white: {
			enabled: "text-blue-500 bg-white hover:bg-blue-50 focus:ring-blue-200",
			disabled: "text-blue-500 bg-white focus:ring-blue-50"
		},
		black: {
			enabled: "text-white bg-gray-900 hover:bg-black focus:ring-blue-200",
			disabled: "text-white bg-gray-700 focus:ring-blue-50"
		},
		destructive: {
			enabled: "text-white bg-red-500 hover:bg-red-600 focus:ring-red-200",
			disabled: "text-white bg-red-200 focus:ring-red-50"
		}
	}

	const styleClasses = styles[style][disabled ? "disabled" : "enabled"];
	const classes = [baseClasses, styleClasses, sizingClasses, more ? "rounded-l" : "rounded"].join(" ");

	const handleToggleDropdown = () => {
		setIsDropdownOpen(!isDropdownOpen);
	}

	if (href) {
		return (
			<a href={href} target={target} {...{ disabled, className: classes + " " + className, onClick }} >
				{children}
			</a>
		)
	}
	else {
		return (
			<div className={"relative flex w-fit rounded-md " + className}>
				<button type="button" {...{ className: classes, disabled, onClick }} >
					{children}
				</button>
				{
					more &&
					<button aria-expanded={isDropdownOpen ? "true" : "false"} aria-controls={dropdownID} aria-label="More options..." className={"px-1 rounded-r ring-0 border-l border-black/20 transition-all focus:ring-4 focus:outline-none " + styleClasses} onClick={handleToggleDropdown} disabled={disabled}>
						<Icon icon="expand_more" />
					</button>
				}

				{
					more &&
					<ul id={dropdownID} hidden={!isDropdownOpen} ref={dropdownRef} className={"absolute overflow-hidden bg-white rounded shadow-lg m-0 p-1 " + (moreTop ? "bottom-full -translate-y-2 right-0" : "top-full translate-y-2 left-0")}>
						{more.map((item, i) =>
							<li className="mb-0" key={i}>
								<button onClick={item.onClick} className="group flex items-center gap-3 w-full rounded-sm text-left whitespace-nowrap py-2 px-3 transition-colors hover:bg-blue-50 hover:text-blue-800 focus:bg-blue-50 focus:text-blue-800 focus:outline-none">
									{item.icon && <Icon icon={item.icon} size={20} className="!p-0 text-gray-400 transition-colors group-hover:text-blue-500 group-focus:text-blue-500" />}
									{item.label}
								</button>
							</li>
						)}
					</ul>
				}
			</div>
		)
	}
}
import React, { useEffect, useRef, useState } from "react";
import Icon from "./Icon";
import RulesList from "./RulesList";
import Switch from "./Switch";
import useAPI from "./hooks/useAPI";
import useAPILoading from "./hooks/useAPILoading";
import DataStore from "../DataStore";

export default function MetaOptionsWrapper({
	option,
	children,
	className,
	disable = false,
}) {
	const [errors, setErrors] = useState([]);
	const isLoading = useAPILoading('settings');
	const inputRef = useRef();

	useEffect(() => {
		DataStore.settings.subscribe((newSettings) => {
			if (!inputRef.current) {
				return
			}

			if (inputRef.current.type === 'checkbox') {
				inputRef.current.checked = newSettings[option]
			}
			else {
				inputRef.current.value = newSettings[option]
			}
		})
	})

	const isError = errors.length > 0;

	const indicatorLoading = <Icon icon={'refresh'} className="text-blue-500" animation="spin" />;
	const indicatorError = <Icon icon={'close'} className="text-red-500" />;

	const onSave = async (evt) => {
		let newValue = evt.target.value;

		if (evt.target.type === 'checkbox') {
			newValue = evt.target.checked;
		}

		const resp = DataStore.settings.set({
			[option]: newValue
		});

		resp.then(() => {
			setErrors([]);
			window.toast?.create('Setting updated!', 'check_circle');
		});

		resp.catch(({ data }) => {
			let errors = [data.details[option]]
			let additional_errors = data.details[option]?.additional_errors
			additional_errors && errors.push(...additional_errors)

			setErrors(errors);

			window.toast?.create("Setting could not be updated.", 'warning');
		})
	}

	return (
		<div className={(isLoading ? "animate-pulse opacity-75 " : "") + className}>
			{
				React.Children.map(children, child => {
					if (child.type === Switch) {
						return React.cloneElement(child, {
							innerRef: inputRef,
							indicator: isLoading ? indicatorLoading : isError ? indicatorError : null,
							disabled: isLoading || disable,
							onChange: onSave,
						})
					}
					else {
						return React.cloneElement(child, {
							innerRef: inputRef,
							indicator: isLoading ? indicatorLoading : isError ? indicatorError : null,
							disabled: isLoading || disable,
							onBlur: onSave,
							onEnter: onSave,
						})
					}
				})
			}

			{errors.length > 0 &&
				<RulesList rules={errors} className={"mt-6 transition-opacity " + (isLoading && "opacity-25")} />
			}
		</div>
	)
}
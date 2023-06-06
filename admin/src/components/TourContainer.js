import React, { useState } from "react";
import IconButton from "./IconButton";
import Title from "./Title";

export default function TourContainer({
	children,
	open = false,
	className = '',
	onClose,
}) {
	const [step, setStep] = useState(0);
	const maxStep = React.Children.count(children) - 1;
	const currentChild = React.Children.toArray(children).find((child, i) => i === step);

	if (!open) {
		return null;
	}

	const navigateHandler = (dir) => {
		let next = (step + dir)

		if (next < 0 || next > maxStep) {
			next = Math.max(Math.min(next, maxStep), 0)
			typeof onClose === 'function' && onClose();
		}

		setStep(next)
	}

	return (
		<div className={" bg-gradient-to-tl from-blue-100 to-blue-50 rounded-md p-5 " + className}>
			<div className="flex items-center justify-between mb-2">
				<Title level={4} className="!mb-0">
					{currentChild.props.title}
				</Title>

				{typeof onClose === 'function' &&
					<IconButton icon="close" onClick={onClose} />
				}
			</div>

			<div>
				{currentChild}
			</div>

			<div className="flex gap-4 mt-5">
				<IconButton icon="chevron_left" disabled={step === 0} onClick={() => navigateHandler(-1)} />

				<IconButton icon="chevron_right" disabled={step === maxStep} onClick={() => navigateHandler(1)} />
			</div>
		</div>
	)
}
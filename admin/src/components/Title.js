import React from "react"

export default function Title({
	level = 2,
	className = '',
	children,
}) {
	level = parseInt(level)

	switch (level) {
		case 1:
			return (
				<h1 className={"text-4xl lg:text-6xl font-semibold mb-8 " + className}>
					{children}
				</h1>
			);
			break;

		default:
		case 2:
			return (
				<h2 className={"text-2xl lg:text-5xl font-semibold mb-6 " + className}>
					{children}
				</h2>
			);
			break;

		case 3:
			return (
				<h3 className={"text-xl lg:text-2xl font-medium mb-4 " + className}>
					{children}
				</h3>
			);
			break;

		case 4:
			return (
				<h4 className={"text-lg font-medium mb-2 " + className}>
					{children}
				</h4>
			);
			break;

		case 5:
			return (
				<h5 className={"font-medium mb-1 " + className}>
					{children}
				</h5>
			);
			break;
	}
}
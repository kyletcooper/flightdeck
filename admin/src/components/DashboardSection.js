import React from "react";
import premiumBackground from '../assets/images/premium-background.png';

export default function DashboardSection({
	title,
	onClick,
	className = '',
	premium = false,
	children
}) {
	let classes = "rounded-lg p-4 md:p-6 lg:p-8 bg-cover text-left ";

	if (premium) {
		classes += "bg-blue-500 text-white "
	}
	else {
		classes += "bg-white text-gray-900 "
	}

	if (onClick) {
		classes += "transition-all border border-transparent ring-0 hover:shadow-lg hover:shadow-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-500"
	}

	classes += className;

	const Tag = onClick ? 'button' : 'section';

	return (
		<Tag className={classes} onClick={onClick} style={{ backgroundImage: premium && `url(${premiumBackground})` }}>
			<h2 className="font-medium text-inherit text-lg mb-2">
				{title}
			</h2>

			<div>
				{children}
			</div>
		</Tag>
	)
}
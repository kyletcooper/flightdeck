import React from "react"
import Icon from "./Icon"

export default function RulesList({
	className = "",
	rules = [],
	loading = false
}) {
	// const allPass = React.Children.map(children, child => child.props.passes).every(el => !!el);

	if (loading) {
		return (
			<div className="flex items-center justify-center gap-4 bg-gray-50 text-gray-500 p-5 rounded animate-pulse">
				<Icon icon="refresh" animation="spin" />
			</div>
		)
	}
	else if (rules.length > 0) {
		rules.sort((a, b) => a.success - b.success);

		return (
			<ol className={"flex flex-col gap-6 " + className}>
				{
					rules.map((rule, i) => (
						<li key={rule.code} className="flex items-start gap-4 m-0 text-sm">
							{
								rule.success ?
									<div className="mt-0.5 rounded border border-gray-300 bg-white">
										<Icon icon="done" size={12} className="!p-0.5 text-gray-500" />
									</div>
									:
									<div className="mt-0.5 rounded border border-red-500 bg-red-500">
										<Icon icon="close" size={12} className="!p-0.5 text-white" />
									</div>
							}

							<div className={rule.success ? 'text-gray-600' : 'font-medium text-gray-900'}>
								{rule.message}
							</div>
						</li>
					))
				}
			</ol>
		)
	}
}
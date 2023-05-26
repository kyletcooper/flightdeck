import React from "react";
import useAPI from "./hooks/useAPI";
import useAPILoading from "./hooks/useAPILoading";
import IconButton from "./IconButton";
import Tooltip from "./Tooltip";

function stringToColor(string) {
	var colors = [
		"#ef4444",
		"#f97316",
		"#f59e0b",
		"#eab308",
		"#84cc16",
		"#22c55e",
		"#10b981",
		"#14b8a6",
		"#06b6d4",
		"#0ea5e9",
		"#3b82f6",
		"#6366f1",
		"#8b5cf6",
		"#a855f7",
		"#d946ef",
		"#ec4899",
		"#f43f5e",
	]

	var hash = 0;
	if (string.length === 0) return hash;
	for (var i = 0; i < string.length; i++) {
		hash = string.charCodeAt(i) + ((hash << 5) - hash);
		hash = hash & hash;
	}
	hash = ((hash % colors.length) + colors.length) % colors.length;
	return colors[hash];
}

export default function IndicatorBar() {
	const [{ address, allowed }] = useAPI('connection');
	const isLoading = useAPILoading('connection');

	if (!allowed) {
		return null;
	}

	const url = window.flightdeck.home_url;

	const handleGoToConnectedSite = () => {
		const connectedDashboardURL = window.location.href.replace(url, address);
		window.open(connectedDashboardURL, '_blank');
	}

	return (
		<div className="flex items-center justify-center gap-2 sticky top-0 min-[601px]:top-[46px] min-[783px]:top-[32px] left-0 right-0 z-50 shadow-lg shadow-black/5 py-2 px-5" style={{ backgroundColor: stringToColor(url) }}>
			<span className="text-white whitespace-nowrap text-ellipsis">
				Working on {url}
			</span>

			<Tooltip title="Go to Connected Site">
				<IconButton disabled={isLoading} style="transparent" icon="redo" size={16} className="text-white -rotate-12" label="Go to Connected Site" onClick={handleGoToConnectedSite} />
			</Tooltip>
		</div>
	)
}
import React, { useState } from "react";
import defaultThumbnail from '../../assets/images/placeholder_pattern.png';
import DashboardSection from "../DashboardSection";
import UpdateDepartureDetailsPanel from "../UpdateDepartureDetailsPanel";
import Icon from "../Icon";
import useAPILoading from "../hooks/useAPILoading";
import useAPI from "../hooks/useAPI";

export default function SectionDepartureConnection() {
	const [isPanelOpen, setIsPanelOpen] = useState(false);
	const [connectionDetails] = useAPI('connection');
	const isLoading = useAPILoading('connection');

	const hasResponseError = connectionDetails?.hasOwnProperty("code");
	const hasConnectionErrors = connectionDetails?.errors?.some(e => !e.success);

	return (
		<>
			<UpdateDepartureDetailsPanel open={isPanelOpen} onClose={() => setIsPanelOpen(false)} />

			<DashboardSection title="Departure Connection">
				<button className={"group w-full rounded-md transition-all hover:bg-gray-100 hover:ring-8 hover:ring-gray-100 focus:outline-none focus:bg-gray-100 focus:ring-8 focus:ring-gray-100 " + (isLoading ? "animate-pulse opacity-50 pointer-events-none" : "")} onClick={() => setIsPanelOpen(true)}>
					<div className="relative w-full aspect-video mb-4 bg-gray-200 rounded overflow-hidden">
						{!isLoading && !hasResponseError &&
							<img src={connectionDetails?.image || defaultThumbnail} className="object-cover absolute inset-0 h-full w-full scale-105" />
						}
					</div>

					{
						<div>
							<div className="flex gap-4 pr-2 items-center">
								<div className="relative w-10 h-10 rounded-full overflow-hidden bg-gray-50">
									<img src={connectionDetails?.favicon} className="w-full h-full scale-105 object-cover" />
									<Icon className="absolute inset-0 w-full h-full transition-all opacity-0 bg-gray-50 scale-75 group-hover:opacity-100 group-hover:scale-110" icon="edit" size="18" />
									<Icon className={"absolute inset-0 w-full h-full transition-all motion-safe:animate-spin text-gray-600 bg-gray-50 " + (isLoading ? "opacity-100" : "opacity-0")} icon="refresh" />
								</div>

								<h3 className={"text-xl lg:text-2xl font-medium " + (hasResponseError ? 'text-gray-400' : '')}>
									{isLoading ? 'Loading...' : hasResponseError ? connectionDetails?.message || 'An error occured.' : connectionDetails?.name || 'No connection set.'}
								</h3>

								{!isLoading &&
									<div className="relative w-3 h-3 ml-auto">
										<div className={"w-full h-full rounded-full " + (hasConnectionErrors ? 'bg-red-500' : 'bg-green-500')}>
										</div>

										<div className={"absolute inset-0 w-full h-full rounded-full opacity-60 motion-safe:animate-ping " + (hasConnectionErrors ? 'bg-red-500' : 'bg-green-500')}>
										</div>
									</div>
								}
							</div>

						</div>
					}

				</button>
			</DashboardSection>
		</>
	)
}
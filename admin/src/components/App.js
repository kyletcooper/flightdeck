import React from "react"
import ToastContainer from "./ToastContainer"
import Title from "./Title"
import Button from "./Button"
import DashboardSection from "./DashboardSection"
import SectionArrivalConnections from "./dashboard/SectionArrivalConnections"
import SectionDepartureConnection from "./dashboard/SectionDepartureConnection"
import SectionDepartures from "./dashboard/SectionDepartures"
import IndicatorBar from "./IndicatorBar"
import SectionLogs from "./dashboard/SectionLogs"

export default function App() {
	return (
		<div>
			<IndicatorBar />
			<ToastContainer />

			<div className="mt-2 p-6 md:p-10 lg:p-12 text-base">

				<Title level="1">
					FlightDeck
				</Title>

				<div className="grid gap-6 md:gap-8 xl:grid-cols-[2fr_1fr] items-start">
					<div className="grid gap-inherit">
						<SectionDepartures />

						<SectionLogs />
					</div>

					<div className="grid gap-inherit">
						<SectionDepartureConnection />

						<SectionArrivalConnections />

						<DashboardSection title="Created by SodaPixel" premium>
							<Button className="w-full" href="https://sodapixel.com" target="_blank" style="white">
								Visit our Website
							</Button>
						</DashboardSection>
					</div>
				</div>
			</div>
		</div>
	)
}
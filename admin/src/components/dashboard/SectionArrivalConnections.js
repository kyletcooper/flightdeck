import React, { useEffect, useState } from "react";
import DataStore from "../../DataStore";
import Button from "../Button";
import DashboardSection from "../DashboardSection";
import Icon from "../Icon";
import Input from "../Input";
import MetaOptionsWrapper from "../MetaOptionsWrapper";
import Panel from "../Panel";
import Switch from "../Switch";
import Tooltip from "../Tooltip";
import TourContainer from "../TourContainer";
import TourStep from "../TourStep";

export default function SectionArrivalConnections() {
	const [isPasswordSet, setIsPasswordSet] = useState(true);

	useEffect(() => {
		DataStore.settings.subscribe(settings => setIsPasswordSet(settings.flightdeck_local_password !== null));
	}, [])

	const [isOpen, setIsOpen] = useState(false);

	return (
		<>
			<Panel title="Arrival Connection Details" open={isOpen} onClose={() => setIsOpen(false)}>
				<div className="h-full p-5 flex flex-col">

					<p className="mb-8">
						Keep a note of this as you can't view it again.
					</p>

					<MetaOptionsWrapper option="flightdeck_local_password" className="mb-5">
						<Input type="password" placeholder="This value is hidden." autoComplete="new-password">
							Arrival Password
						</Input>
					</MetaOptionsWrapper>

					<TourContainer open className="mt-auto">
						<TourStep title="How Arrivals are Secured">
							<p>
								Before another FlightDeck site can connect to this one, they'll need to provide the password you set here.
							</p>

							<p>
								Make sure it can't be guessed and is secure. You won't be able to view it after you save it.
							</p>
						</TourStep>

						<TourStep title="How Arrivals are Secured">
							<p>
								We recommend you only enable arrival connections when you're about to send data and disable it afterward.
							</p>

							<p>
								For your protection, site data can only be sent over secure HTTPS connections.
							</p>
						</TourStep>
					</TourContainer>

				</div>
			</Panel>

			<DashboardSection title="Arrival Connections">
				<Tooltip title="You must set a connection password first." disable={isPasswordSet} className="w-full">
					<MetaOptionsWrapper option="flightdeck_allow_connections" disable={!isPasswordSet} className="w-full">
						<Switch inline>
							Allow connections to this site?
						</Switch>
					</MetaOptionsWrapper>
				</Tooltip>

				<Button style="secondary" className="!w-full mt-8" onClick={() => setIsOpen(true)}>
					<Icon icon="lock" />

					{isPasswordSet ? 'Change Connection Password' : 'Set Connection Password'}
				</Button>
			</DashboardSection>
		</>
	)
}
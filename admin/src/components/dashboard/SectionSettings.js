import React, { useState } from "react";
import Panel from "../Panel";
import MetaOptionsWrapper from "../MetaOptionsWrapper";
import Switch from "../Switch";
import Button from "../Button";
import Icon from "../Icon";
import Collapse from "../Collapse";

export default function SectionSettings() {
	const [isOpen, setIsOpen] = useState(false);

	return (
		<>
			<Panel title="Settings" open={isOpen} onClose={() => setIsOpen(false)}>

				<Collapse title="Lock Changes" open className="!border-t-0">
					<p className="mb-6">
						Prevent users from updating posts, media & pages on this site. This is a visual change only and not a security feature.
					</p>

					<MetaOptionsWrapper option="flightdeck_lock_local_changes" className="font-medium">
						<Switch inline>
							Lock changes on this site?
						</Switch>
					</MetaOptionsWrapper>
				</Collapse>

				<Collapse title="Indicator Bar" open>
					<p className="mb-6">
						The FlightDeck indicator bar will show you which site you're on when logged in to prevent confusion.
					</p>

					<MetaOptionsWrapper option="flightdeck_lock_show_indicator_bar_backend" className="font-medium mb-3">
						<Switch inline>
							Show on admin dashboard?
						</Switch>
					</MetaOptionsWrapper>

					<MetaOptionsWrapper option="flightdeck_lock_show_indicator_bar_frontend" className="font-medium">
						<Switch inline>
							Show on front-end?
						</Switch>
					</MetaOptionsWrapper>
				</Collapse>

			</Panel>

			<Button style="white" onClick={() => setIsOpen(true)}>
				<Icon icon="settings" />

				Settings
			</Button>
		</>
	)
}
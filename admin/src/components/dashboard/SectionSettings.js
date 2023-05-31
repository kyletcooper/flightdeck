import React, { useState } from "react";
import Panel from "../Panel";
import MetaOptionsWrapper from "../MetaOptionsWrapper";
import Switch from "../Switch";
import Button from "../Button";
import Icon from "../Icon";

export default function SectionSettings() {
	const [isOpen, setIsOpen] = useState(false);

	return (
		<>
			<Panel title="Settings" open={isOpen} onClose={() => setIsOpen(false)}>
				<div className="h-full p-5 flex flex-col">
					<MetaOptionsWrapper option="flightdeck_lock_local_changes">
						<Switch inline>
							Lock changes on this site?
						</Switch>
					</MetaOptionsWrapper>
				</div>
			</Panel>

			<Button style="white" onClick={() => setIsOpen(true)}>
				<Icon icon="settings" />

				Settings
			</Button>
		</>
	)
}
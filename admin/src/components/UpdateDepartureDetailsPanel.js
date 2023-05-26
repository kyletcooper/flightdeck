import React from "react";
import Chip from "./Chip";
import Collapse from "./Collapse";
import ConnectionStatus from "./ConnectionStatus";
import useAPI from "./hooks/useAPI";
import useAPILoading from "./hooks/useAPILoading";
import Input from "./Input";
import MetaOptionsWrapper from "./MetaOptionsWrapper";
import Panel from "./Panel";
import RulesList from "./RulesList";
import TourContainer from "./TourContainer";
import TourStep from "./TourStep";

export default function UpdateDepartureDetailsPanel({
	open = false,
	onClose,
}) {
	const [{ errors, warnings }] = useAPI('connection')
	const isLoading = useAPILoading('connection')

	const hasErrors = errors?.some(e => !e.success);
	const hasWarnings = warnings?.some(e => !e.success);

	return (
		<Panel title="Departure Connection Details" {...{ open, onClose }}>
			<div className="h-full flex flex-col">

				<div className="p-5">
					<p className="mb-5">
						Add a connection to a different WordPress site with FlightDeck installed. You'll need the connection password you set on the other site.
					</p>

					<ConnectionStatus status={isLoading ? "loading" : hasErrors ? "error" : "success"} />
				</div>

				<Collapse title="Departure Details">
					<div className="flex flex-col gap-5">
						<MetaOptionsWrapper option="flightdeck_foreign_address">
							<Input type="url">
								Connection Address
							</Input>
						</MetaOptionsWrapper>

						<MetaOptionsWrapper option="flightdeck_foreign_password">
							<Input type="password" placeholder="This value is hidden.">
								Connection Password
							</Input>
						</MetaOptionsWrapper>

						<TourContainer open>
							<TourStep title="Connecting your Sites">
								<p>
									FlightDeck connections are one way, meaning you send content to overwrite the data on a different site.
								</p>

								<p>
									For example, you might want to work on a staging site and then send those changes to a live site.
								</p>
							</TourStep>

							<TourStep title="Connecting your Sites">
								<p>
									To connect to another site, you'll need to set a Connection Password and enable arrivals on the recieving site.
								</p>

								<p>
									Then you can enter the connection password and the website's URL address here.
								</p>
							</TourStep>
						</TourContainer>
					</div>
				</Collapse>

				<Collapse title="Connection Status" chip={hasErrors && <Chip color="red" icon="error">Errors found</Chip>}>
					<RulesList rules={errors} className={"transition-opacity " + (isLoading ? "opacity-50" : "")} />
				</Collapse>

				<Collapse title="Preflight Checks" chip={hasWarnings && <Chip color="orange" icon="warning">Warnings found</Chip>}>
					<RulesList rules={warnings} className={"transition-opacity " + (isLoading ? "opacity-50" : "")} />
				</Collapse>

			</div>
		</Panel>
	)
}
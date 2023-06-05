import React, { useState } from "react";

import FilePicker from "../filepicker/FilePicker";
import SyncPanel from "../syncpanel/SyncPanel";
import DashboardSection from "../DashboardSection";

import PlaneWhite1 from "../../assets/images/plane_white_1.png";
import PlaneWhite2 from "../../assets/images/plane_white_2.png";
import Button from "../Button";
import Title from "../Title";
import Icon from "../Icon";
import RecordsPicker from "../tablepicker/RecordsPicker";

export default function SectionDepartures() {
	const [fileSelection, setFileSelection] = useState([]);
	const [isSyncFilesOpen, setIsSyncFilesOpen] = useState(false);

	const [recordsSelection, setRecordsSelection] = useState([]);
	const [isSyncRecordsOpen, setIsSyncRecordsOpen] = useState(false);

	return (
		<DashboardSection title="Departures">
			<p>
				You can use departures to send content from this site to your connected site. The content you send from here will overwrite the date on your connected site.
			</p>

			<div className="grid gap-6 md:grid-cols-2 mt-6">
				<div className="flex flex-col h-full p-4 md:p-6 bg-cover rounded-md" style={{ backgroundImage: `url(${PlaneWhite1})` }}>
					<Title level={3} className="!mb-2">
						Files & Media
					</Title>

					<p className="mb-12">
						Send files & media to your departure connection.
					</p>

					<Button className="mt-auto w-full" onClick={() => setIsSyncFilesOpen(true)} style='primary'>
						<Icon icon="folder" />
						Transfer Files & Media
					</Button>
				</div>

				<SyncPanel title="Sync Files & Media" selection={fileSelection.map(f => f.path)} type="file" open={isSyncFilesOpen} onClose={() => setIsSyncFilesOpen(false)} onReset={() => setFileSelection([])} >
					<FilePicker onChange={setFileSelection} />
				</SyncPanel>

				<div className="flex flex-col h-full p-4 md:p-6 bg-cover rounded-md" style={{ backgroundImage: `url(${PlaneWhite2})` }}>
					<Title level={3} className="!mb-2">
						Database Records
					</Title>

					<p className="mb-12">
						Send posts, pages & other database records.
					</p>

					<Button className="mt-auto w-full" onClick={() => setIsSyncRecordsOpen(true)} style='primary'>
						<Icon icon="table_chart" />
						Transfer Database Records
					</Button>
				</div>

				<SyncPanel title="Sync Database" selection={recordsSelection} type="database" open={isSyncRecordsOpen} onClose={() => setIsSyncRecordsOpen(false)} onReset={() => setRecordsSelection([])}>
					<RecordsPicker onChange={setRecordsSelection} />
				</SyncPanel>
			</div>
		</DashboardSection>
	)
}
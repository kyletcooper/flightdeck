import React, { useContext } from "react"
import { FileManagerContext } from "./FilePicker"
import FilePickerItem from "./FilePickerItem"

export default function FilePickerList({
	parent,
	className = ''
}) {
	const fileManager = useContext(FileManagerContext);
	const descendants = fileManager.getByParent(parent).sort((a, b) => {
		if (a.type === b.type) return a.name > b.name ? 1 : -1;
		return a.type > b.type ? 1 : -1;
	})

	if (descendants.length < 1) {
		return null;
	}

	return (
		<div className={"flex flex-col " + className}>
			<ol className="rounded overflow-hidden">
				{descendants.map(file =>
					<FilePickerItem file={file} key={file.path} />
				)}
			</ol>
		</div>
	)
}
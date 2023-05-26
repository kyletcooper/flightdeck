import React, { useContext } from "react"
import { FileManagerContext } from "./FilePicker"
import FileIcon from "./FileIcon"
import FilePickerList from "./FilePickerList"

export default function FilePickerItem({
	file
}) {
	const fileManager = useContext(FileManagerContext);
	const isSelected = fileManager.isVisuallySelected(file);

	return (
		<li className="m-0">
			<button
				className={"flex items-center w-full gap-2 p-3 pl-2 text-left border border-transparent transition-all ring-0 ring-transparent focus-visible:outline-none focus-visible:border-blue-500 focus-visible:ring-blue-200 focus-visible:ring-4 " + (isSelected ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'hover:bg-gray-50')}
				onClick={() => fileManager.toggleFile(file)}
				onDoubleClick={() => fileManager.toggleDirExpanded(file)}
			>
				<FileIcon file={file} className={'transition-colors ' + (isSelected && 'text-blue-500')} />

				<span className="grow text-gray-900 text-sm text-medium whitespace-nowrap text-ellipsis overflow-hidden">
					{file.name}
				</span>

				<span className="ml-auto text-xs text-gray-400 whitespace-nowrap">
					{file.lastmodified}
				</span>
			</button>

			{
				file.type === 'dir' &&
				<FilePickerList parent={file} className="pl-1 ml-1 my-1 border-l border-gray-200" />
			}
		</li>
	)
}
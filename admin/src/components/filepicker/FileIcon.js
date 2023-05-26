import React from "react";
import Icon from "../Icon";

export default function FileIcon({
	file,
	className,
}) {
	const extension = file?.name.split('.').pop();

	let colorClass = 'text-gray-400';
	let icon = 'insert_drive_file';

	if (file?.type === 'dir') {
		icon = 'folder'
		colorClass = 'text-gray-600'
	}
	else {
		switch (extension) {
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'webp':
				colorClass = "text-red-500";
				icon = "image";
				break;

			case 'pdf':
				colorClass = "text-red-500";
				icon = "picture_as_pdf";
				break;

			case 'docx':
				colorClass = "text-blue-500";
				icon = "insert_drive_file";
				break;

			case 'xlsx':
				colorClass = "text-green-500";
				icon = "insert_drive_file";
				break;

			case 'mp3':
			case 'wav':
			case 'aac':
			case 'aiff':
			case 'wma':
				colorClass = "text-red-500";
				icon = "music_note";
				break;

			case 'mp4':
			case 'webm':
			case 'mov':
			case 'avi':
			case 'wmv':
				colorClass = "text-red-500";
				icon = "videocam";
				break;

			case 'html':
				colorClass = "text-orange-500";
				icon = "html";
				break;

			case 'js':
				colorClass = "text-yellow-500";
				icon = "javascript";
				break;

			case 'css':
				colorClass = "text-sky-500";
				icon = "css";
				break;

			case 'php':
				colorClass = "text-violet-600";
				icon = "php";
				break;
		}
	}

	return (
		<Icon icon={icon} className={colorClass + ' ' + className} />
	)
}
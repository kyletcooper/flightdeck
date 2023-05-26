import React from "react";
import Icon from "../Icon";

export default function TablePickerItemIcon({
	table,
	selected = false,
}) {
	const icons = {
		commentmeta: 'mode_comment',
		comments: 'mode_comment',

		links: 'link',

		options: 'settings',

		postmeta: 'insert_drive_file',
		posts: 'insert_drive_file',

		term_relationships: 'local_offer',
		termmeta: 'local_offer',
		term_taxonomy: 'local_offer',
		terms: 'local_offer',

		usermeta: 'person',
		users: 'person',
	}

	let icon = 'table_chart';

	for (const key in icons) {
		if (table.name.includes(key)) {
			icon = icons[key];
		}
	}

	return <Icon icon={icon} className={'p-3 transition-colors ' + (selected ? 'bg-white text-blue-500' : 'bg-white text-gray-700')} />
}
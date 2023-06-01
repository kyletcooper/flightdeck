import React, { useEffect, useState } from "react";
import DataStore from "../../DataStore";
import Icon from "../Icon";
import TablePickerItem from "./TablePickerItem";

export default function TablePicker({
	onChange = () => { },
	onExpand = () => { },
}) {
	const [selection, setSelection] = useState([])
	const [isLoading, setIsLoading] = useState(false)
	const [tables, setTables] = useState([])

	const PickerManager = {
		set: function (newSelection) {
			setSelection(newSelection)
			onChange(newSelection)
		},

		has: function (table) {
			return selection.includes(table.name)
		},

		add: function (table) {
			if (!this.has(table)) {
				this.set([...selection, table.name])
			}
		},

		remove: function (table) {
			if (this.has(table))
				this.set(selection.filter(el => el !== table.name))
		},

		toggle: function (table) {
			this.has(table) ? this.remove(table) : this.add(table)
		},

		selectAll: function () {
			if (selection.length === tables.length) {
				this.set([]);
			}
			else {
				this.set(tables.map(table => table.name));
			}
		},
	}

	useEffect(() => {
		DataStore.tables.loading(setIsLoading);
		DataStore.tables.subscribe(setTables);

		const shortcutHandler = (evt) => {
			if (evt.ctrlKey) {
				switch (evt.key) {
					case "a":
						evt.preventDefault();
						PickerManager.selectAll()
						break;
				}
			}
		}

		document.addEventListener('keydown', shortcutHandler);

		return () => {
			document.removeEventListener('keydown', shortcutHandler);
		}
	}, [PickerManager]);

	return (
		<div className={"flex flex-col relative h-full transition-opacity " + (isLoading && "opacity-50")}>
			{
				isLoading && <div className="sticky top-1/2 left-1/2 -translate-x-1/2 h-0 w-fit"><Icon icon="refresh" animation="spin" size="48" className="relative bottom-12" /></div>
			}

			<ul className="grid gap-1 m-1">
				{tables.map(table => (
					<TablePickerItem key={table.name} table={table} onClick={() => PickerManager.toggle(table)} onDoubleClick={() => { onExpand(table) }} selected={PickerManager.has(table)} />
				))}
			</ul>
		</div>
	)
}
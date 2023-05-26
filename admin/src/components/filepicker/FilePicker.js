import React, { createContext, useEffect, useState } from "react"
import FilePickerList from "./FilePickerList"
import { wp_rest_api_json } from "../../helpers/wpajax";
import Icon from "../Icon";

export const FileManagerContext = createContext({});

export default function FilePicker({
	className,
	onChange = () => { },
}) {
	const [isLoading, setIsLoading] = useState(true)
	const [files, setFiles] = useState([])

	const fileManager = {
		files,
		setFiles,

		getByPath: function (path) {
			return files.find(item => item.path === path);
		},

		getFilesWithoutFile: function (file) {
			return this.files.filter(x => x.path !== file.path);
		},

		getFileDescendants: function (file) {
			return this.files.filter(item => item.path.startsWith(file.path + "/") && item.path !== file.path);
		},

		getParent: function (file) {
			const parentPath = "/" + file.path.split("/").filter(x => x.length).slice(0, -1).join("/");
			return this.getByPath(parentPath);
		},

		getFileAscendants: function (file) {
			const split = file.path.split("/").filter(x => x.length);
			const ascendants = [];

			for (let i = 1; i < split.length; i++) {
				const parentPath = "/" + split.slice(0, i).join("/");
				const parentFile = this.getByPath(parentPath);

				if (parentFile) ascendants.push(parentFile);
			}

			return ascendants;
		},

		isFileAscendantSelected: function (file) {
			return this.getFileAscendants(file).some(item => item.selected === true);
		},

		isVisuallySelected: function (file) {
			if (file.selected === true) {
				return true;
			}

			// Visually selected by parent
			if (this.isFileAscendantSelected(file)) {
				return 1;
			}

			return undefined;
		},

		recursiveDeselectSelfAndParentSelectPeers: function (file) {
			const parent = this.getParent(file);

			if (parent) {
				const peers = this.getByParent(parent);
				peers.forEach(peer => peer.selected = true);

				this.recursiveDeselectSelfAndParentSelectPeers(parent);
			}

			file.selected = undefined;
		},

		toggleFile: function (file, evt) {
			if (file.type === 'dir') {
				this.getFileDescendants(file).forEach(file => file.selected = undefined);
			}

			if (this.isFileAscendantSelected(file)) {
				this.recursiveDeselectSelfAndParentSelectPeers(file);
			}
			else {
				file.selected = !file.selected;
			}

			setFiles(this.files);
			onChange(fileManager.getSelected());
		},

		selectAll: function () {
			this.files.forEach(file => file.selected = true)
			setFiles(this.files);
			onChange(fileManager.getSelected());
		},

		deselectAll: function () {
			this.files.forEach(file => file.selected = undefined)
			setFiles(this.files);
			onChange(fileManager.getSelected());
		},

		toggleSelectAll: function () {
			if (this.getVisuallySelected().length === this.files.length) {
				this.deselectAll();
			}
			else {
				this.selectAll();
			}
		},

		getSelected: function () {
			return files.filter(x => x.selected);
		},

		getVisuallySelected: function () {
			return files.filter(files => this.isVisuallySelected(files));
		},

		getByParent: function (parent) {
			return files.filter(x => x.parent === (parent?.path || ''));
		},

		toggleDirExpanded: function (file) {
			if (this.getFileDescendants(file).length > 0) {
				this.shrinkDir(file);
			}
			else {
				this.expandDir(file);
			}
		},

		shrinkDir: function (dir) {
			setFiles(this.files.filter(file => this.getParent(file)?.path !== dir.path))
			onChange(fileManager.getSelected());
		},

		expandDir: async function (file) {
			if (file === null || (file.type === 'dir' && this.getByParent(file).length === 0)) {
				setIsLoading(true);

				const subfiles = await wp_rest_api_json('flightdeck/v1/files', 'GET', {
					path: file?.path || ''
				})

				setFiles([...this.files, ...subfiles])
				setIsLoading(false);
			}
		},
	}

	useEffect(() => {
		fileManager.expandDir(null);
	}, [])

	useEffect(() => {
		const shortcutHandler = (evt) => {
			if (evt.ctrlKey) {
				switch (evt.key) {
					case 'a':
						evt.preventDefault();
						fileManager.toggleSelectAll();
						break;
				}
			}
		}

		document.addEventListener('keydown', shortcutHandler);

		return () => {
			document.removeEventListener('keydown', shortcutHandler);
		}
	}, [fileManager])

	return (
		<div className={"h-full relative transition-opacity border border-transparent ring-0 focus-visible:ring-4 focus-visible:ring-blue-200 focus-visible:border-blue-500 " + (isLoading && "opacity-20 animate-pulse") + " " + className}>
			{
				isLoading && <div className="sticky top-1/2 left-1/2 -translate-x-1/2 h-0 w-fit"><Icon icon="refresh" animation="spin" size="48" className="relative bottom-12" /></div>
			}

			<FileManagerContext.Provider value={fileManager}>
				<FilePickerList parent={null} />
			</FileManagerContext.Provider>
		</div>
	)
}
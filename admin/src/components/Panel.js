import React, { useEffect, useRef } from "react"
import IconButton from "./IconButton"
import Title from "./Title";

export default function Panel({
	title,
	children,
	open = false,
	onClose = () => { }
}) {
	const dialogRef = useRef();

	useEffect(() => {
		if (open && !dialogRef.current?.open) {
			dialogRef.current?.showModal()
			document.body.style.overflow = 'hidden'
		}
		else {
			dialogRef.current?.close()
			document.body.style.overflow = 'auto'
		}
	}, [open])

	return (
		<dialog className="hidden open:grid grid-cols-[1fr_auto] fixed inset-0 z-[99999] m-0 p-0 h-full w-full max-h-none max-w-none bg-transparent backdrop:bg-gray-900/20" ref={dialogRef} onClose={onClose} >
			<div className="w-full h-full" onClick={onClose}></div>

			<aside className="flex flex-col h-full w-[35rem] max-w-[90vw] max-h-screen m-0 bg-white ml-auto border-l border-gray-300 shadow-2xl">
				<header className="flex items-center justify-between border-b border-gray-300 p-3 pl-5">
					<Title level={3} className="!mb-0">
						{title}
					</Title>

					<IconButton icon="close" onClick={onClose} />
				</header>

				<main className="grow overflow-y-scroll overscroll-contain">
					{open && children}
				</main>
			</aside>

		</dialog>
	)
}
<?php

// Inject content protection script for non-admin users & non-logged in visitors
add_action( 'wp_footer', 'frontend_restriction' , 99);

function frontend_restriction() {
	if ( is_user_logged_in() && current_user_can( 'administrator' ) ) return; ?>
<script>
	(function () {
		// Disable unwanted actions globally
		["contextmenu", "selectstart", "copy", "cut", "paste", "dragstart"].forEach(evt => {
			document.addEventListener(evt, e => e.preventDefault());
		});

		// Disable common keyboard shortcuts
		document.addEventListener("keydown", e => {
			const blocked = [
				123, // F12
				85,  // U
				83,  // S
				67,  // C
				88,  // X
				86,  // V
				80   // P
			];

			// Windows/Linux shortcuts
			if (
				e.keyCode === 123 || // F12
				(e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) || // Ctrl+Shift+I/J
				(e.ctrlKey && blocked.includes(e.keyCode))
			) {
				e.preventDefault();
				e.stopPropagation();
			}

			// MacOS equivalents (⌘ key = metaKey, ⌥ = altKey, ⇧ = shiftKey)
			if (
				// ⌘+⌥+U
				(e.metaKey && e.altKey && e.keyCode === 85) ||
				// ⌘+⇧+U
				(e.metaKey && e.shiftKey && e.keyCode === 85) ||
				// Ctrl+⇧+U
				(e.ctrlKey && e.shiftKey && e.keyCode === 85)
			) {
				e.preventDefault();
				e.stopPropagation();
			}
		});

		const style = document.createElement("style");
		style.textContent = `body{-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;} img{-webkit-user-drag: none;user-drag: none;pointer-events: auto;}`;
		document.head.appendChild(style);

		// Lock images (initial + dynamic)
		function lockImages() {
			document.querySelectorAll("img:not([data-locked])").forEach(img => {
				img.setAttribute("draggable", "false");
				img.dataset.locked = "1";
				img.addEventListener("contextmenu", e => e.preventDefault());
			});
		}
		lockImages();

		new MutationObserver(lockImages).observe(document.body, { childList: true, subtree: true });
	})();
</script>
<?php }
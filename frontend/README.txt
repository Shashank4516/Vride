Frontend static assets for VRide.

public/js holds browser scripts loaded by backend/public/*.php URLs (typically /js/...).
The symlink backend/public/js -> ../../frontend/public/js keeps PHP static paths working on macOS/Linux.

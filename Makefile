CHROME ?= /usr/lib64/chromium-browser/headless_shell
CHROME_ARGS ?= --headless --disable-extensions --no-sandbox

all:
	gprbuild -p -P gnat/test_driver.gpr
	gprbuild -p -P gnat/test_sync_call_server.gpr
	gprbuild -p -P gnat/test_sync_call_wasm_client.gpr -c remote.ads
	gprbuild -p -P gnat/test_sync_call_wasm_client.gpr

check: all Tests/echo/adawebpack.mjs
	.objs/test_driver/driver-run $(CHROME) $(CHROME_ARGS)

Tests/echo/adawebpack.mjs:
	ln -s /usr/share/adawebpack/adawebpack.mjs Tests/echo/
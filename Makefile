all:
	gprbuild -j0 -p -P gnat/garlic.gpr
	gprinstall   -p -P gnat/garlic.gpr --prefix=. --sources-subdir=lib/garlic


Source Organization
-------------------

The GLADE distribution contains different components organized
in separate directories:

  1. Garlic
     A PCS (partition communication subsystem) which is a high level
     communication layer that provides several classical services
     available in a distributed system (partition id management, name
     services, distributed termination, ...) and that accommodates
     several network protocols and communication sub-systems.

  2. Dist
     A partitioning tool called "gnatdist" and its configuration
     language which allow you to divide your program into a number of
     independent partitions and specify the machines where the
     individual partitions are to execute on.

  4. Examples
     A set of examples to demonstrate GLADE capabilities. Each example
     is self-contained and can be built separately by running 'make'
     in the subdirectory.
 

Binary Installation
-------------------

Binaries will be installed under the prefix specified by --prefix in
the gprinstall step (by default GNAT install directory).

    gprbuild -p -P gnat/garlic.gpr
    gprbuild -p -P gnat/gnatdist.gpr
    gprinstall -p -P gnat/garlic.gpr --prefix=${PREFIX} --sources-subdir=lib/garlic
    gprinstall -p -P gnat/gnatdist.gpr --prefix=${PREFIX} --mode=usage

will result in installing all GLADE components in the following
directories:

    ${PREFIX}/bin               : the executables, gnatdist and xe-gcc
    ${PREFIX}/lib/garlic        : Garlic object files

The directory ${PREFIX} may not to exist before you install the package.

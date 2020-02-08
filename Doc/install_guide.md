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

  3. Ada
     A subset of GNAT __gnat_version__ sources necessary to build GLADE.

  4. Examples
     A set of examples to demonstrate GLADE capabilities. Each example
     is self-contained and can be built separately by running 'make'
     in the subdirectory.
 

Binary Installation
-------------------

Binaries will be installed under the prefix specified by --prefix in
the configuration step (by default /usr/local).
      ./configure --prefix=${TARGET}
      make install
will result in installing all GLADE components in the following
directories:

    ${TARGET}/bin               : the executables, gnatdist and xe-gcc
    ${TARGET}/lib/garlic        : Garlic object files

The directory ${TARGET} has to exist before you install the package.

Major Configuration options:
-----------------------------

    --enable-debug          Turn on debugging options
    --without-launching     Don't include launching facilities
    --with-protocols        Enumerate protocol list
    --with-filters          Enumerate filter list
    --with-platform=letter  Select another platform's specific files (cross)
    --with-extra-libs=libs  Add extra libraries when building a program
    --srcdir=path           Enable building with Vpaths
    --build=name            Define the build machine
    --host=name             Define the host machine
    --target=name           Define the target machine 
    --prefix=path           Define the installation prefix


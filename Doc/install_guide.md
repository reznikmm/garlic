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
 
Using with Alire
----------------

Alire has two crates: `gnatdist_garlic` and `garlic`.
The first one is used to install the partitioning tool.
You can install it in advance with 

```shell
alr install [--prefix=DIR]
```

(`DIR=$HOME/.alire` by default) and add `{prefix}/bin` to your `PATH`.
Or you can add it as a dependency of your crate then no `PATH`
manipulation is required.

The `garlic` crate is the PCS library itself. Add it as a dependency
to your crate and `gnatdist` will locate it via `GARLIC_ALIRE_PREFIX`
environment variable set by Alire.

Consider next example scenario:

* Create `client` binary crate: `alr -n init --bin client; cd client`

* Add `garlic` and `gnatdist_garlic` dependencies and build them:
  ```shell
  alr with garlic
  alr with gnatdist_garlic
  alr action -r post-fetch
  alr exec -- gprbuild -P gnatdist
  ```

* Populate `src/` directory with `Examples/Bank/*`:
  ```shell
  cp -v <garlic>/Examples/Bank/* src/
  mv -v src/simcity.cfg .
  ```

* Use `gnatdist` to build partitions:
  ```shell
  alr exec -- gnatdist -v -aIsrc simcity
  ```

## Custom PCS

You can use a customized version of partition communication subsystem.
To do this, create a library crate which name starts with `dsa_rts`
derived from `garlic` sources and use it instead of `garlic`. Make sure
your custom library crate (e.g., `dsa_rts`) provides the necessary compiled
files (`.ali`, `.ads`, `.adb`, and a static library `libgarlic.a`)
in a location discoverable by `gnatdist` - `lib/garlic` directory at the root
of your PCS crate. The application crate shoudn't depend on `garlic` in this
case.


Installation without Alire
--------------------------

Binaries should be installed under a prefix specified by `--prefix` in
the `gprinstall` step (by default GNAT install directory). Commands

    gprbuild -p -P gnat/garlic.gpr
    gprbuild -p -P gnat/gnatdist.gpr
    gprinstall -p -P gnat/garlic.gpr --prefix=${PREFIX} --sources-subdir=lib/garlic
    gprinstall -p -P gnat/gnatdist.gpr --prefix=${PREFIX} --mode=usage

will result in installing all GLADE components in the following
directories:

    ${PREFIX}/bin               : the executables, gnatdist and xe-gcc
    ${PREFIX}/lib/garlic        : Garlic object files

The directory `${PREFIX}` may not to exist before you install the package.

NOTE. Make sure `lib/garlic` has `.ali`, `.ad[sb]` and `libgarlic.a` to
let `gnatdist` find PCS.

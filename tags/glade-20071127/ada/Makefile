
CP := cp --verbose

.PHONY : gnat
.PHONY : clean

ifdef GNATSRC

GNAT_Specs :=			\
		./ali.adb    	\
		./ali.ads    	\
		./ali-util.adb	\
		./ali-util.ads	\
		./alloc.ads	    \
		./atree.adb	    \
		./atree.ads	    \
		./binderr.adb	\
		./binderr.ads	\
		./butil.adb	    \
		./butil.ads	    \
		./casing.adb	\
		./casing.ads	\
		./csets.adb    	\
		./csets.ads	    \
		./debug.adb	    \
		./debug.ads	    \
		./einfo.adb	    \
		./einfo.ads	    \
		./elists.adb	\
		./elists.ads	\
		./errout.adb	\
		./errout.ads	\
		./fmap.adb    	\
		./fmap.ads	    \
		./fname.adb	    \
		./fname.ads	    \
		./fname-sf.adb	\
		./fname-sf.ads	\
		./fname-uf.adb	\
		./fname-uf.ads	\
		./gnatvsn.ads	\
		./hostparm.ads	\
		./krunch.adb	\
		./krunch.ads	\
		./lib.adb    	\
		./lib.ads	    \
		./lib-list.adb	\
		./lib-sort.adb	\
		./make.adb	    \
		./make.ads	    \
		./makeusg.adb	\
		./makeusg.ads	\
		./mlib.adb	    \
		./mlib.ads	    \
		./mlib-fil.adb	\
		./mlib-fil.ads	\
		./mlib-prj.adb	\
		./mlib-prj.ads	\
		./mlib-tgt.adb	\
		./mlib-tgt.ads	\
		./mlib-utl.adb	\
		./mlib-utl.ads	\
		./namet.adb    	\
		./namet.ads	    \
		./nlists.adb	\
		./nlists.ads	\
		./opt.adb	    \
		./opt.ads	    \
		./osint.adb	    \
		./osint.ads	    \
		./osint-m.adb	\
		./osint-m.ads	\
		./output.adb	\
		./output.ads	\
		./prj.adb	    \
		./prj.ads    	\
		./prj-attr.adb	\
		./prj-attr.ads	\
		./prj-com.adb	\
		./prj-com.ads	\
		./prj-dect.adb	\
		./prj-dect.ads	\
		./prj-env.adb	\
		./prj-env.ads	\
		./prj-ext.adb	\
		./prj-ext.ads	\
		./prj-nmsc.adb	\
		./prj-nmsc.ads	\
		./prj-pars.adb	\
		./prj-pars.ads	\
		./prj-part.adb	\
		./prj-part.ads	\
		./prj-proc.adb	\
		./prj-proc.ads	\
		./prj-strt.adb	\
		./prj-strt.ads	\
		./prj-tree.adb	\
		./prj-tree.ads	\
		./prj-util.adb	\
		./prj-util.ads	\
		./rident.ads	\
		./scans.adb	    \
		./scans.ads	    \
		./scn.adb	    \
		./scn.ads	    \
		./sdefault.ads	\
		./sfn_scan.adb	\
		./sfn_scan.ads	\
		./sinfo.adb	    \
		./sinfo.ads    	\
		./sinfo-cn.adb	\
		./sinfo-cn.ads	\
		./sinput.adb	\
		./sinput.ads	\
		./sinput-l.adb	\
		./sinput-l.ads	\
		./sinput-p.adb	\
		./sinput-p.ads	\
		./snames.adb	\
		./snames.ads	\
		./stand.adb	    \
		./stand.ads	    \
		./stringt.adb	\
		./stringt.ads	\
		./style.ads	    \
		./stylesw.adb	\
		./stylesw.ads	\
		./switch.adb	\
		./switch.ads	\
		./switch-m.adb	\
		./switch-m.ads	\
		./table.adb	    \
		./table.ads	    \
		./tree_io.adb	\
		./tree_io.ads	\
		./types.adb	    \
		./types.ads	    \
		./uintp.adb	    \
		./uintp.ads	    \
		./uname.adb	    \
		./uname.ads	    \
		./urealp.adb	\
		./urealp.ads	\
		./usage.adb	    \
		./usage.ads	    \
		./validsw.adb	\
		./validsw.ads	\
		./widechar.adb	\
		./widechar.ads	\
		./tempdir.ads	\
		./targparm.ads	\
		./prj-err.ads	\
		./scng.ads		\
		./styleg.ads	\
		./errutil.ads	\
		./err_vars.ads	\
		./sinput-c.ads	\
		./erroutc.ads


GNAT_Body :=			\
		./tempdir.adb	\
		./targparm.adb	\
		./prj-err.adb	\
		./scng.adb		\
		./styleg.adb	\
		./errutil.adb	\
		./sinput-c.adb	\
		./erroutc.adb	\
		./gnatvsn.adb


GNAT_C :=				\
		./version.c		\
		./version.h		\
		./prefix.c		\
		./prefix.h		\
		./config.h		\
		./system.h		\
		./coretypes.h		\
		./tm.h			\
		./auto-host.h		\
		./hwint.h		\
		./filenames.h

gnat :					\
		$(GNAT_Specs)	\
		${GNAT_Body}	\
		${GNAT_C}

clean:
	$(RM) $(GNAT_Specs)
	$(RM) $(GNAT_Body)
	$(RM) $(GNAT_C)

./%.ads :				\
		${GNATSRC}/%.ads
	$(CP) $(<) $(@)

./%.adb :				\
		${GNATSRC}/%.adb
	$(CP) $(<) $(@)

./%.c :					\
		${GNATSRC}/../%.c
	$(CP) $(<) $(@)

./%.h :					\
		${GNATSRC}/../%.h
	$(CP) $(<) $(@)

./%.c :					\
		${GNATSRC}/../%.c
	$(CP) $(<) $(@)


./%.h :					\
		${GNATSRC}/../../include/%.h
	$(CP) $(<) $(@)

./%.h :					\
		${GNATSRC}/../../../i686-pc-linux-gnu/gcc/%.h
	$(CP) $(<) $(@)

else

    $(error Please point GNATSRC to original gnat sources.)

endif

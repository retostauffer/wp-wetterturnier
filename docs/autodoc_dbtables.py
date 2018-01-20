# -------------------------------------------------------------------
# - NAME:        autodoc_dbtables.py
# - AUTHOR:      Reto Stauffer
# - DATE:        2018-01-19
# -------------------------------------------------------------------
# - DESCRIPTION: Helper script to create the sphinx documentation.
#                Automatically creates table schemes from the
#                database. Reuqires database access and has to be
#                run manually if changes are made to the database
#                structure.
#                Uses autodoc_dbtables.conf as input config file.
# -------------------------------------------------------------------
# - EDITORIAL:   2018-01-19, RS: Created file on thinkreto.
# -------------------------------------------------------------------
# - L@ST MODIFIED: 2018-01-19 14:09 on marvin
# -------------------------------------------------------------------

import logging
logging.basicConfig(level=logging.INFO)
log = logging.getLogger("dbtables")

# ---------------------------------------------------------------
# Reading required config file. Creats a config object with
# required information.
# ---------------------------------------------------------------
class setup( object ):

    def __init__( self, file = "autodoc_dbtables.conf"  ):

        import sys, os, re
        if not os.path.isfile(file):
            log.error("Sorry, cannot find file \"{:s}\"".format(file))
            sys.exit(9)

        # Use ConfigParser to parse config file
        from ConfigParser import ConfigParser
        CNF = ConfigParser(); CNF.read(file)

        # Getting general config
        try:
            self.outdir    = CNF.get("general","outdir")
            self.deleteold = CNF.getboolean("general","deleteold")
            self.overwrite = CNF.getboolean("general","overwrite")
        except Exception as e:
            self.error("Misspecified [general] section in config file!")
            print e
            sys.exit(9)

        # Read configuration for obs database
        self.dbconf = {}

        r = re.compile("^database\s+([\S]+)$")
        databases = [r.match(x).group(1) for x in filter(r.match,CNF.sections())]
        for db in databases:
            try:
                section = "database {:s}".format(db)
                self.dbconf[db] = {}
                self.dbconf[db]["host"]     = CNF.get(section,"host")
                self.dbconf[db]["user"]     = CNF.get(section,"user")
                self.dbconf[db]["passwd"]   = CNF.get(section,"passwd")
                self.dbconf[db]["dbname"]   = CNF.get(section,"dbname")
            except Exception as e:
                log.error("Misspecification in [database {0:s} config in config file".format(db))
                print e
                sys.exit(9)
            try:
                self.dbconf[db]["prefix"]   = CNF.get(section,"prefix")
            except:
                self.dbconf[db]["prefix"]   = None

            # Reading table specification
            r = re.compile("^table\s+{:s}\s+([\S]+)$".format(db))
            self.dbconf[db]["tables"] = {}
            for tab in filter( r.match, CNF.sections() ):
                table = r.findall( tab )[0]
                if self.dbconf[db]["prefix"]:
                    table = "{0:s}{1:s}".format( self.dbconf[db]["prefix"], table )
                # Reading caption
                try:
                    caption = CNF.get(tab,"caption")
                except:
                    caption = "No caption set"
                # Reading 'showonly' configuration. If set only these columns will
                # be shown in the table, a special 'ignored' column is added at the bottom.
                # of the output.
                try:
                    showonly = CNF.get(tab,"showonly").split(",")
                except:
                    showonly = None

                # Append
                self.dbconf[db]["tables"][table] = {"name":table,"caption":caption,'showonly':showonly}

        self.show()
        self._check_dir_()
        self._delete_old_()

    def _check_dir_( self ):
        """Check output directory, if not existing: create
        """
        import os, sys
        if not os.path.isdir( self.outdir ):
            try:
                os.mkdir( self.outdir )
            except Exception as e:
                log.error("Problems creating output directory!")
                print e
                sys.exit(9)

    def _delete_old_( self ):
        """If flag deleteold is set: delete output files first."""
        if not self.deleteold: return
        import os, sys, glob
        files = glob.glob( "{:s}/*.rsx".format(self.outdir) )
        if len(files) == 0: return
        log.info(" - Delete old files first:")
        for file in files:
            log.info("   Remove {:s}".format(file))
            os.remove( file )

    def databases( self ):
        """Returns databases loaded from the config file."""
        return self.dbconf.keys()

    def connect( self, db ):
        """Connect to database.

        Args:
            db (:obj:`str): Database name (as specified in config file).
        
        Returns:
            MySQL db handler: Returns database connection handler.
        """
        import MySQLdb, sys
        if not db in self.databases():
            log.error("Cannot connect to \"{:s}\", no configuration found.".format(db))
            sys.exit(9)
        # Connect and return MySQLdb connection handler.
        return MySQLdb.connect( host   = self.dbconf[db]["host"],  
                                user   = self.dbconf[db]["user"],
                                passwd = self.dbconf[db]["passwd"],
                                db     = self.dbconf[db]["dbname"],)

    def create( self, dbname ):
        """Creates the output files.

        Args:
            dbname (:obj:`str`): Name of the database config from the config file.
        Returns:
            Returns nothing but creates the output files in the output directory
            as for the tables as specified in the config file.
        """

        import sys, os
        log.info("Processing \"{:s}\"".format(dbname))
        if not dbname in self.dbconf.keys():
            log.error("Cannot create output for \"{0:s}\". \"{0:s}\" not specified.".format(dbname))
            sys.exit(9)
        # Connect to database. Exits if not configured.
        db = self.connect( dbname )
        for key,table in self.dbconf[dbname]["tables"].iteritems():
            outfile = "{0:s}/{1:s}.rsx".format(self.outdir,table["name"])
            if os.path.isfile(outfile) and not self.overwrite:
                log.info(" - File \"{0:s}\" exists and overwrite not allowed: skip.".format(outfile))
                continue

            # Else create output file.
            log.info(" - Create output file \"{:s}\"".format( outfile ))
            fid = open( outfile, "w" )
            fid.write( self.create_sphinx_table_output( db, table["name"],
                       table["caption"], table["showonly"] ) )
            fid.close()

        # Close this database connection
        db.close()

    # simply shows read config on stdout
    def show( self ):

        log.info("General configuration:")
        log.info(" - {0:20s} {1:s}".format("Output directory:",self.outdir))
        log.info(" - {0:20s} {1:s}".format("Delete old output:",str(self.deleteold)))
        log.info(" - {0:20s} {1:s}".format("Allow overwrite:",str(self.overwrite)))
        log.info("Database configuration:")
        for db in ["obs","wp"]:
            log.info(" - {0:20s} {1:s}".format("Database:",self.dbconf[db]["dbname"]))
            log.info("   {0:20s} {1:s}".format("Hostname:",self.dbconf[db]["host"]))
            log.info("   {0:20s} {1:s}".format("Username:",self.dbconf[db]["user"]))
            log.info("   {0:20s} {1:s}".format("Password:","******"))
            for key,tab in self.dbconf[db]["tables"].iteritems():
                log.info("   Table {0:s}: {1:s}".format(tab["name"],tab["caption"]))

    def create_sphinx_table_output( self, db, table, caption, showonly = None ):
        """This is the function why this script exists.
        Create output of databas table structure using
        CSV table style for sphinx documentation.
        see http://docutils.sourceforge.net/docs/ref/rst/directives.html#csv-table

        Args:
            db (:obj:`MySQLdb.connect`): MySQLdb database connection handler.
            table (:obj:`str`): Database table name.
            caption (:obj:`str`): Table caption.
            showonly (:obj:`list`): List of column names, or None. If set (list)
                only the columns from this list will be shown.

        Returns:
            str: Returns a string containing the full sphinx-doc csv table style
            database structure description.
        """
    
        res = []
    
        # Database request
        cur = db.cursor()
        cur.execute( "SHOW COLUMNS FROM {:s};".format(table) )
        cols = cur.description
    
        # Header
        caption = "[Autogenerated table scheme of table \"{0:s}] {1:s}\"".format(table,caption)
        res.append( "\n.. csv-table:: {:s}\n".format( caption ) + \
                    "    :header: " + ", ".join( [ "\"{:s}\"".format(col[0]) for col in cols] ) + \
                    "\n" )
        
        # Content
        ignored = 0
        for row in cur.fetchall():
            if showonly:
                if not row[0] in showonly:
                    ignored += 1
                    continue
            res.append( "    {:s}".format( ",".join( [ "\"{:s}\"".format(x) for x in row ] ) ) )

        # If there were ignored columns: add ... at the end of the table
        if ignored > 0:
            res.append( "    {:s}".format( ",".join( ["\"...\""]*len(cols) )))
        res.append( "\n\n" )

        # Cretae index information
        dbkeys = {}
        cur.execute( "SHOW KEYS FROM {0:s};".format(table) )
        desc = [x[0] for x in cur.description]
        for rec in cur.fetchall():
            keyname = rec[desc.index("Key_name")]
            if not keyname in dbkeys.keys(): dbkeys[keyname] = {}
            dbkeys[keyname]["non_unique"] = rec[desc.index("Non_unique")]
            dbkeys[keyname]["col_{:d}".format(rec[desc.index("Seq_in_index")])] = \
                    rec[desc.index("Column_name")]

        if len(dbkeys) > 0:
            for name in dbkeys.keys():
                key_res = ["* Non-unique key" if dbkeys[name]["non_unique"]==1 else "* **Unique-key**"]
                key_res.append("named *{0:s}* on".format(name))
                on = []
                for i in range(0,len(dbkeys[name])-1):
                    on.append( dbkeys[name]["col_{:d}".format(i+1)] )
                key_res.append("``({:s})``".format( ", ".join(on) ) )

                res.append( " ".join(key_res) )

        res.append( "\n\n" )
                    

        return "\n".join(res)
    
# Main script
if __name__ == "__main__":

    import sys

    # Initialize the object, reads config.
    obj = setup()

    # Processing information
    for dbname in obj.databases():
        obj.create( dbname )
    

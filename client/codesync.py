#!/usr/bin/env python
import sys
import http.client
import requests
import glob, os
import os.path, time, datetime
import re

class CodeSync:
    ErrorCounter = 0
    Executed = []
    
    def query(server, port, uri, projects):
        conn = http.client.HTTPConnection(server, port)
        conn.request("bGET", uri)
        r = conn.getresponse()

        content = r.getheader("Content-type")
        if content == "application/octet-stream":
            return r.read()
        else:
            for line in r.read().decode("utf-8").splitlines():
                parts = line.split("\t")
                localObject  = {}
                # Empty line
                if parts[0] == "":
                    continue
                # The line is defining the hosting server
                if parts[0] == "S":
                    continue
                # The line is defining the version to be used
                elif parts[0] == "V":
                    if parts[1] != 'v2':
                        print("Operation aborted:")
                        print("CodeSync server is running CodeSync."+parts[1]+" which is not supported locally.")
                        return 0
                    else:
                        continue
                # The line is defining a pre-built query to sync a folder
                elif parts[0] == "D" or parts[0] == "D+":
                    localObject = CodeSync.objectFromQuery("D", parts[2], projects)
                # The line is defining a pre-built query to sync a file
                elif parts[0] == "F" or parts[0] == "F+":
                    localObject = CodeSync.objectFromQuery("F", parts[2], projects)

                # Pulling from server, locally creating/updating
                dt = time.mktime(datetime.datetime.strptime(parts[1], "%Y-%m-%d %H:%M:%S").timetuple())
                if (not localObject['exists']) or (localObject['lastedit'] < dt):
                    print("PULL-" + localObject['type'] + "\t" + localObject['path'])
                    if(localObject['type'] == "D"):
                        try:
                            os.makedirs(localObject['path'])
                        except FileExistsError:
                            pass
                    else:
                        pfur = uri.split("/")
                        pfur = "/" + pfur[1] + "/" + pfur[2] + "/pull/file/" + localObject['serverPath']
                        fcontent = CodeSync.query(server, port, pfur, projects)
                        
                        fh = open(localObject['path'], "wb")
                        fh.write(fcontent)
                        fh.close()
                        
                # Pushing to server
                elif localObject['lastedit'] > dt:
                    pfur = uri.split("/")
                    fullObj = "folder"
                    if localObject["type"] == "F" or localObject["type"] == "F+":
                        fullObj = "file"
                        
                    pfur = "/" + pfur[1] + "/" + pfur[2] + "/push/" + fullObj + "/" + localObject['serverPath']
                    CodeSync.pushObject(server, port, localObject['type'], pfur, localObject['path'])

                try:
                    CodeSync.Executed.append([localObject['type'], localObject['serverPath'], localObject['path']])
                except:
                    pass

    def pushObject(server, port, otype, query, localPath):
        success = False
        error = ""
        
        if otype == "D":
            r = requests.post("http://" + server + query)
            if "PUSH-SUCCESSFUL" in r.text:
                success = True
            else:
                error = r.text.split('\r\n')
                
        if otype == "F":
            files = {'file': open(localPath, 'rb')}
            r = requests.post("http://" + server + query, files=files)
            if "PUSH-SUCCESSFUL" in r.text:
                success = True
                print(r.text)
            else:
                error = r.text.split('\r\n')
                
        if success:    
            print("PUSH-" + otype + "\t" + localPath)
        else:
            CodeSync.ErrorCounter += 1
            print("*ERR-" + otype + "\t Failed to push " + localPath)
            print("\t FAILED QUERY: " + "http://" + server + query)
            for line in error:
                print("\t   " + str(line))
        
    def objectFromQuery(otype, query, projectsToPath):
        result = {
                'code': 0,
                'type': '',
                'path': '',
                'serverPath': '',
                'project': '',
                'extension': '',
                'exists': False,
                'lastedit': 0,
            }
        
        if otype == "D":
            result['path'] = re.search('(.*)/pull/folder/(.*)', query).group(2)
            result['type'] = 'D'
        elif otype == "F":
            result['path'] = re.search('(.*)/pull/file/(.*)', query).group(2)
            result['type'] = 'F'
            result['extension'] = result['path'].split(".")[1]
        else:
            return { 'code': 1 }

        result['serverPath'] = result['path']

        result['project'] = result['path'].split("/")[0]
        result['path'] = projectsToPath[result['project']] + "/" + result['path']
        
        result['exists'] = os.path.exists(result['path'])
        if result['exists']:
            result['lastedit'] = os.path.getmtime(result['path'])

        return result
        
    
class ConsoleInterface:
    version = 0
    device = "device"
    server = "Unknown"
    defaultProjectsRoot = "/"
    projects = {}

    srvPort = 80
    mode = ""
    obj = ""
    op = []
    
    queries = []

    def __init__(self):
        self.version = 2
        self.loadCfg()

    def loadCfg(self):
        cfg = open('codesync.cfg')
        for line in cfg.read().splitlines():
            fields = line.split("\t")
            if fields[0] == "server":
                self.server = fields[1]
            elif fields[0] == "device":
                self.device = fields[1]
            elif fields[0] == "default-projects-root":
                self.defaultProjectsRoot = fields[1]
            elif fields[0] == "project":
                self.projects[fields[1]] = fields[2]
        
    def process(self, args):
        last = ''
        error = 0
        if len(args) <= 1:
            self.showHelp()
            
        for arg in args:
            if error > 0:
                break
            if arg == 'codesync.py' or arg == 'codesync':
                continue
            
            if arg == '-v2':
                # Force version to version2
                self.version = 2
            elif arg == '-port':
                # Set TCP port for HTTP connection
                last = arg
            elif arg == '-op':
                # Perform a query manually
                last = arg
            elif arg == '-sync':
                # Perform some kind of synchronization
                last = arg
            elif arg == '--help':
                # Show help
                self.showHelp()
                break
            else:
                # Process as input value rather than symbol
                if last == '':
                    error = 1
                elif last == '-port':
                    self.srvPort = arg
                elif last == '-op':
                    self.mode = "Manual"
                    self.op.append(arg)
                elif last == '-sync':
                    if arg == 'all':
                        self.mode = 'SyncAll'
                    elif arg == 'project':
                        last = '-sync project'
                    elif arg == 'folder':
                        last = '-sync folder'
                    elif arg == 'file':
                        last = '-sync file'
                elif last == '-sync project':
                    self.mode = 'SyncProject'
                    self.obj = arg
                elif last == '-sync folder':
                    self.mode = 'SyncFolder'
                    self.obj = arg
                elif last == '-sync file':
                    self.mode = 'SyncFile'
                    self.obj = arg
                else:
                    error = 1

        if error > 0:
            self.showHelp(True)
        else:
            self.buildQuery()
            self.launchOps()

    def buildQuery(self):
        if self.mode == "Manual":
            return self.op
        elif self.mode == "SyncAll":
            return self.createQuerySyncAll()
        elif self.mode == "SyncProject":
            return self.createQuerySyncProject(self.obj)
        elif self.mode == "SyncFolder":
            return self.createQuerySyncFolder(self.obj)
        elif self.mode == "SyncFile":
            return self.createQuerySyncFile(self.obj)
        else:
            return 1000

    def createQuerySyncAll(self):
        lastResult = 0
        for key in self.projects:
            value = self.projects[key]
            currentResult = self.createQuerySyncProject(key)
            if currentResult > lastResult:
                lastResult = currentResult
        return lastResult

    def createQuerySyncProject(self, name):
        self.op.append("/pull/project/" + name)
        return 0

    def createQuerySyncFolder(self, name):
        self.op.append("/pull/folder/" + name)
        return 0

    def createQuerySyncFile(self, name):
        self.op.append("/pull/file/" + name)
        return 0

    def launchOps(self):
        devstr = "/v" + str(self.version) + "/device:" + self.device
        # Looping through root operations
        for operation in self.op:
            CodeSync.query(self.server, self.srvPort, devstr + operation, self.projects)

        
    def showHelp(self, badInput=False):
        if badInput:
            print("Unrecognized syntax. Here's the help:")
            print("")
            
        print("CodeSync v" + str(self.version) + " client is working OK")
        print("For more information about CodeSync please visit: ")
        print("  * http://alessandromaggio.com/project/codesync/")
        print("  * http://github.com/alessandromaggio/codesync")
        print("")
        print("")
        print("Quick reference: ")
        print("python codesync.py [-v2] { -op url |")
        print("                           -sync {all | project name |")
        print("                                  folder path |")
        print("                                  file path }")
        print("                         }")
        print("")
        print(" -v2          Force CodeSync to work with version 2")
        print(" -op url      Execute a custom query (the url)")
        print(" -sync")
        print("    project   Synchronize an entire project")
        print("    folder    Synchronize a folder")
        print("    file      Synchronize a single file")
        print("")
        print("You can tune default settings by editing codesync.cfg")

try:
    console = ConsoleInterface()
    console.process(sys.argv)
except Exception as e:
    print("CodeSync experienced an error")
    print("The exception details are: " + str(e))

#!/usr/bin/env python
import sys
import http.client

class CodeSync:
    def query(server, port):
        conn = http.client.HTTPConnection(server, port)
        conn.request("GET", "/")
        return conn.getresponse().read()

class ConsoleInterface:
    version = 0

    def __init__(self):
        self.version = 2
        
    def process(self, args):
        last = ''
        error = 0
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
                    self.port = arg
                elif last == '-op':
                    self.op = arg
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
                elif last == '-sync folder':
                elif last == '-sync file':
                else:
                    error = 1

        if error > 0:
            self.showHelp()
            
    def showHelp(self):
        print("This is help")
                

console = ConsoleInterface()
console.process(sys.argv)

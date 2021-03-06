## What is codesync?
#### A quick overview
CodeSync is an HTTP-based application with a simple purpose: synchronize folders and files between devices. It allows bidirectional synchronization - meaning that a device can update the files of the other(s) and vice versa, but it is designed in an asynchronous manner: most of changes will be made onto a specific device (the **codesync server**) and all other devices (the **codesync clients**) will sync to that, occasionally committing changes to some files to the server.

#### Purpose of codesync
CodeSync is designed to provide file synchronization specificially for developers. It has been designed in such a way that the developer can write his code on his laptop, workstation or whatsoever and then have all his devices that will run that specific code automatically synchronized to the new code. This is quite useful to do some debugging real time on devices that are not equipped with an user-friendly text editor.
Particularly, codesync address the following issues:
* It eliminates the need to code directly on CLI-only environment such as small linux distributions
* It eliminates the need to manually transfer files via FTP, TFTP, SSH, Telnet and such
* It automatically keeps code consistency
* It supports selective synchronization with different devices and projects
* It has been designed to be easy to configure and eventually tune

#### What codesync is not
CodeSync is not an alternative to file hosting services such as Google Drive or Dropbox, nor a collaboration tool for developers such as github. Even if CodeSync could be used for both of the stated uses, they are not what codesync has been designed for. However, CodeSync is a valid solution for unilateral code provisioning.


## Installation & Configuration
#### What do I need to do exactly to have my file synchronized?
In order to have your devices automatically synchronizing your files you will need to install two things: the codesync server on the device where you plan to write the code onto (such as your computer); and the codesync client on all devices that will rune the code. Then you will need to configure each client with a device name and specifiy on the server where are the files to be synchronized. That's basically all.
#### Server setup
In order to install the server you will need to install an Apache webserver first. Apache is widely used and it is available for many differnet operating systems. Note that you MUST use Apache since CodeSync uses .htaccess files that are specific to Apache, so other web servers will not work. Once done that, you will have to put all codesync files in its root folder.

1. Install apache (https://httpd.apache.org/) OR if you are under Windows and you want something simpler, then I'd suggest you XAMPP (https://www.apachefriends.org)
2. Once the webserver is installed, find the root folder (for example C:/xampp/htdocs) and everything in it, then put there all the files contained in the "server" folder of CodeSync
3. Test CodeSync by visiting the webpage http://localhost/
4. Now that you have the server working, you are going to want to have it accessible from at least your local network, so you will need to assign a static IP address to your computer (or obtain the same result with DHCP reservation) and open the HTTP port (80) on your firewall. There wouldn't be point in explaining deeply these procedures because they significatively vary between different operating system, but these are simple things to do and you'll find plenty of guides for your OS over the Internet

#### Client setup
To setup the client you need Python. Python is embedded in most linux distribution, whereas you will have to download it if you are on windows. Once you have installed it, you can do the following.
1. Launch the command prompt / terminal / CLI and execute the command *pip install requests*, in order to do that you will need internet connectivity
2. After that, go to the folder where you put codesync files for the client and configure the *codesync.cfg* file to define default local path and such
3. When you are ready, using a terminal go to the directory where your *codesync.py* is and then run *python codesync.py* in order to have a list of available commands

## I need more help!
Fortunately, we have two resources you may want to check:
* The project website, at http://alessandromaggio.com/project/codesync/
* The codesync project itself, with its built-in wiki, at http://github.com/alessandromaggio/codesync

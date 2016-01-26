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
In order to install the server you will need to install a webserver first. Apache is widely used and it is available for many differnet operating systems. Once done that, you will have to put all codesync files in its root folder.

import os
from pydrive.auth import GoogleAuth
from pydrive.drive import GoogleDrive
from base_db import baseDb


adobe_folder_id = 38638
gauth = GoogleAuth()
drive = GoogleDrive(gauth)
gQuery = "'root' in parents and trashed=false"
folder_id = '32424234SAMPLEHASH'  # Root shared drive
local_root_path = 'data'
f = open("failed.txt", "w+")
MIMETYPES = {
    'application/vnd.google-apps.document': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.google-apps.spreadsheet': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.google-apps.presentation': 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
}
EXTENSTIONS = {
    'application/vnd.google-apps.document': '.docx',
    'application/vnd.google-apps.spreadsheet': '.xlsx',
    'application/vnd.google-apps.presentation': '.pptx'
}


def authenticate_google():
    gauth.LoadCredentialsFile("creds.dat")
    if gauth.credentials is None:
        gauth.LocalWebserverAuth()  # client_secrets.json on same director
    elif gauth.access_token_expired:
        gauth.Refresh()
    else:
        gauth.Authorize()
    gauth.SaveCredentialsFile("creds.dat")


def query_drive_files():
    file_list = drive.ListFile({'q': "'%s' in parents and trashed=false" % folder_id}).GetList()
    for id, file in enumerate(file_list):
        db = baseDb()
        db.save_data(row_id=id, title=file['title'], file=file)
        print('title: %s, id: %s' % (file['title'],
                                     file['mimeType'],
                                     file['id']))


def escape_fname(name):
    return name.replace('/', '_')


def create_folder(path, name):
    os.makedirs('{}{}'.format(path, escape_fname(name)), exist_ok=True)


def g_download(folder_id, root):
    file_list = drive.ListFile({'q': "'%s' in parents and trashed=false" % folder_id}).GetList()
    for file in file_list:
        if file['mimeType'].split('.')[-1] == 'folder':
            foldername = escape_fname(file['title'])
            create_folder(root, foldername)
            g_download(file['id'], '{}{}/'.format(root, foldername))
        else:
            download_mimetype = None
            filename = escape_fname(file['title'])
            filename = '{}{}'.format(root, filename)
            try:
                print('DOWNLOADING:', filename, 'ID:', file['id'])
                if file['mimeType'] in MIMETYPES:
                    download_mimetype = MIMETYPES[file['mimeType']]

                    file.GetContentFile(
                        filename+EXTENSTIONS[file['mimeType']], mimetype=download_mimetype)
                else:
                    file.GetContentFile(filename)
            except Exception:
                print('FAILED')
                f.write(filename+'\n')


def create_db():
    db = ktpDb()
    resp = db.create_adobe_table()
    print('Creating DB table: ', resp.table_status)

###########################################################################
# RUNTIME STARTS HERE


authenticate_google() # Authenticates GApp and User Login.
query_drive_files()
g_download(folder_id, local_root_path+'/')
f.close()


# create_db()

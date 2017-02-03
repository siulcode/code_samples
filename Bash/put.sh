#!/bin/sh
file="hello.txt"
date=`date +%Y%m%d`
bucket=djis-dev-sascdm
resource="/${bucket}/${file}"
contentType="text"
dateValue=`date -R`
stringToSign="PUT\n\n${contentType}\n${dateValue}\n${resource}"
s3Key=AKIAJETOYZBO3LCVYFHA
s3Secret=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
signature=`echo -en ${stringToSign} | openssl sha1 -hmac ${s3Secret} -binary | base64`
curl -X PUT -T "${file}" \
    -H "Host: ${bucket}.s3.amazonaws.com" \
    -H "Date: ${dateValue}" \
    -H "Content-Type: ${contentType}" \
    -H "Authorization: AWS ${s3Key}:${signature}" \
    https://${bucket}.s3.amazonaws.com/${file}

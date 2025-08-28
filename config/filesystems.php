<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            
        ],
        
    //   'gcs' => [
    //       "driver" => "gcs",
    //       "project_id"=> "famcam-bluestoneapps",
    //         'key_file' => [
    //               "type"=> "service_account",
    //               "project_id"=> "famcam-bluestoneapps",
    //               "private_key_id"=> "f29f8250614e7b5be9e5f870b603add53e537dfe",
    //               "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDehrTYB+wpbmM1\nm1esJ//64yKKrb5lh4uyw6jaRdePfTJmBnqW6jVvutH4WEjM0BiOIDD/m3yowtNU\nu/2nFrokP0DsT4hGU8YUJqJRX360MQEG0EddOadLajs6rTBlxFhsDsRVetT+Ml3+\n1/Z4pzALV9CAWDwzKsPLtMu46lHsabU7A0ISXLN8mRzg+yWbWDQKJiUHVvfTKf0J\nSvFiJ9dqKLWFixJiCGRvrOHdMYIZCIqyrB2R2t5ww6E1eaxH7FjylgEDrXRcuOix\n/rcZ5Ek+ux+nUfBKdDy6EvY4r+PxtxSNMub6FoivRiWSApqAJxTl0dM7Be0atGU/\nRxIN13bBAgMBAAECggEAChZ7Pkvlpd8uOjDnoZNSpHIFj9nehsXFTH5YOkTuR7/6\nN+vVROArOfxzJloCOt64LkFBV2UaGqByaiX3ksqR97CyZcQZPm7vzNwPgbbdjJeo\nH0Hz6kMB1YZkVv2g8wwaohNtTR67ZyarYjEyKDgUEn+RoS/e0sb+h06T6bcdraTr\new/JysOpRI1UvCHa959afkwWUjeGNocmZjI2Rn5RsGkqzbKe+jCIoRUI7ajgzp3/\nOHt+JICxEjwnKnlaW04nhvdbrnmraUPLc/uYFhjB6vAHSTvUSbkKt+kj4dqHuAy9\ny+SYWtwawiYLPo/sHEViTpLTxUZyMqbv8T9jPImV3QKBgQD6ZcJGY0AWDIw9MYKl\nlmndgHT6TdEoi6RbLeiNFTPPEC5qfBprOWEgwFmu3uwiHVTUPdV1YuS0kJ/5MJ9P\nFVa3bY3l0XgNv8i6lito7pe+duwnnAQuGx2QuLVLKX1VnA+qu1ApHcfDgebffLOs\nD0faOfnDpjt01UwM+DJMFylcOwKBgQDjgU4BwRTbdYNp8UA5lIrkZcv/W1s03WuH\nuADlqVvC1qw1P5ogf0ro5hHMTzKi+lcZE1Kuu4NXNJAa6SYeaW2qCisevkjiWnx7\nrXE9/xvxbklORiU3JrGKD1ZGlwvRaHwVwMqB75HBGwko6CSQ2QEg/1PaJjiMrx85\ngBhDr8fVMwKBgBcI93dcOBAPNXOHs3oiPTj4UUqZkA1H02Xj8knQUoTQ+0QmiJOm\noq3NpDJ3JYf55Mzlp1Z2M4385Aqbk3xF/UVAmdYzj/TL2N55ZCLyGBmYfR8jtiq7\nqdufcmYoufP/OF2/f59YswDkWWXj5e+FNFn6DWUXTM7xtF5ZEt93HgHzAoGBALBy\noBqyMsgG+1ZWmzZNY+/CmBZEN5fnxzdq2Z9F2/pgXw2pd5OOxn2dut4X6rEGsjir\niOwmWLZw+Pc2Lq9Vm41O96SPdp3ACl4t5e9shbKZk9dWhhShOP9X59U13x+aBusk\nLKvcL9Jycoc8jOeetsZt4Q4HqMJMLE5/b9JHahiLAoGAQ+jnY0glkl5JPEFjGJuA\nuzKugy26VYTqiqWEMizMEp/TOV9gwGIBKB3HXg1jzbA8wVjz9wzwAZdRlkBiWi/Y\nvN/JuT0rvhrw3CA0CqLwvA8wZgvlWqXt+/kq7kSYUv2tQ4Ca+xnkthghIWQTzwZT\nVIy090YT9/lRqfhNh3suMPM=\n-----END PRIVATE KEY-----\n",
    //               "client_email"=> "famcam@famcam-bluestoneapps.iam.gserviceaccount.com",
    //               "client_id"=> "100626720340456059528",
    //               "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
    //               "token_uri"=> "https://oauth2.googleapis.com/token",
    //               "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
    //               "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/famcam%40famcam-bluestoneapps.iam.gserviceaccount.com",
    //               "universe_domain"=> "googleapis.com"
    //                   ],
    //                   'bucket'=>'famcam',
    //                   'visibility' => 'public',
    //              ],
    //           ],
              ],
        'gcs' => [
            'driver' => 'gcs',
             "project_id"=> "famcam-bluestoneapps",
                'key_file' => [
                  "type"=> "service_account",
                  "project_id"=> "famcam-bluestoneapps",
                  "private_key_id"=> "f29f8250614e7b5be9e5f870b603add53e537dfe",
                  "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDehrTYB+wpbmM1\nm1esJ//64yKKrb5lh4uyw6jaRdePfTJmBnqW6jVvutH4WEjM0BiOIDD/m3yowtNU\nu/2nFrokP0DsT4hGU8YUJqJRX360MQEG0EddOadLajs6rTBlxFhsDsRVetT+Ml3+\n1/Z4pzALV9CAWDwzKsPLtMu46lHsabU7A0ISXLN8mRzg+yWbWDQKJiUHVvfTKf0J\nSvFiJ9dqKLWFixJiCGRvrOHdMYIZCIqyrB2R2t5ww6E1eaxH7FjylgEDrXRcuOix\n/rcZ5Ek+ux+nUfBKdDy6EvY4r+PxtxSNMub6FoivRiWSApqAJxTl0dM7Be0atGU/\nRxIN13bBAgMBAAECggEAChZ7Pkvlpd8uOjDnoZNSpHIFj9nehsXFTH5YOkTuR7/6\nN+vVROArOfxzJloCOt64LkFBV2UaGqByaiX3ksqR97CyZcQZPm7vzNwPgbbdjJeo\nH0Hz6kMB1YZkVv2g8wwaohNtTR67ZyarYjEyKDgUEn+RoS/e0sb+h06T6bcdraTr\new/JysOpRI1UvCHa959afkwWUjeGNocmZjI2Rn5RsGkqzbKe+jCIoRUI7ajgzp3/\nOHt+JICxEjwnKnlaW04nhvdbrnmraUPLc/uYFhjB6vAHSTvUSbkKt+kj4dqHuAy9\ny+SYWtwawiYLPo/sHEViTpLTxUZyMqbv8T9jPImV3QKBgQD6ZcJGY0AWDIw9MYKl\nlmndgHT6TdEoi6RbLeiNFTPPEC5qfBprOWEgwFmu3uwiHVTUPdV1YuS0kJ/5MJ9P\nFVa3bY3l0XgNv8i6lito7pe+duwnnAQuGx2QuLVLKX1VnA+qu1ApHcfDgebffLOs\nD0faOfnDpjt01UwM+DJMFylcOwKBgQDjgU4BwRTbdYNp8UA5lIrkZcv/W1s03WuH\nuADlqVvC1qw1P5ogf0ro5hHMTzKi+lcZE1Kuu4NXNJAa6SYeaW2qCisevkjiWnx7\nrXE9/xvxbklORiU3JrGKD1ZGlwvRaHwVwMqB75HBGwko6CSQ2QEg/1PaJjiMrx85\ngBhDr8fVMwKBgBcI93dcOBAPNXOHs3oiPTj4UUqZkA1H02Xj8knQUoTQ+0QmiJOm\noq3NpDJ3JYf55Mzlp1Z2M4385Aqbk3xF/UVAmdYzj/TL2N55ZCLyGBmYfR8jtiq7\nqdufcmYoufP/OF2/f59YswDkWWXj5e+FNFn6DWUXTM7xtF5ZEt93HgHzAoGBALBy\noBqyMsgG+1ZWmzZNY+/CmBZEN5fnxzdq2Z9F2/pgXw2pd5OOxn2dut4X6rEGsjir\niOwmWLZw+Pc2Lq9Vm41O96SPdp3ACl4t5e9shbKZk9dWhhShOP9X59U13x+aBusk\nLKvcL9Jycoc8jOeetsZt4Q4HqMJMLE5/b9JHahiLAoGAQ+jnY0glkl5JPEFjGJuA\nuzKugy26VYTqiqWEMizMEp/TOV9gwGIBKB3HXg1jzbA8wVjz9wzwAZdRlkBiWi/Y\nvN/JuT0rvhrw3CA0CqLwvA8wZgvlWqXt+/kq7kSYUv2tQ4Ca+xnkthghIWQTzwZT\nVIy090YT9/lRqfhNh3suMPM=\n-----END PRIVATE KEY-----\n",
                  "client_email"=> "famcam@famcam-bluestoneapps.iam.gserviceaccount.com",
                  "client_id"=> "100626720340456059528",
                  "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
                  "token_uri"=> "https://oauth2.googleapis.com/token",
                  "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
                  "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/famcam%40famcam-bluestoneapps.iam.gserviceaccount.com",
                  "universe_domain"=>"googleapis.com"
                ],
            'bucket'=>'fam-cam-input',
            'path_prefix' => '',
            'storage_api_uri' => '',
            'visibility' => 'public',
        ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
    

];

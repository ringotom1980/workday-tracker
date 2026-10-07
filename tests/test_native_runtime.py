"""Local Apache tests with synthetic files only. Never uses production credentials."""
import os,pathlib,tempfile,subprocess,json,time,urllib.request,urllib.error,socket,shutil,collections
repo=pathlib.Path(__file__).resolve().parents[1];tools=pathlib.Path(os.environ.get('WORKDAY_TEST_TOOLS','/tmp/workday-tools'))
env=os.environ.copy();env['LD_LIBRARY_PATH']=str(tools/'usr/lib/x86_64-linux-gnu')
php=tools/'usr/bin/php8.4';apache=tools/'usr/sbin/apache2';modules=tools/'usr/lib/apache2/modules'
with tempfile.TemporaryDirectory(prefix='workday-test-') as tmp:
 base=pathlib.Path(tmp);doc=base/'doc';doc.mkdir();runtime=doc/'workday-runtime-v1'
 subprocess.run(['python3',str(repo/'deployment/build.py'),str(runtime),'--source','81e122a00d0e9462d8d783a3b3e8d1e8d366c4f5'],check=True)
 manifest=json.loads((repo/'deployment/manifest.json').read_text())
 assert sorted(p.relative_to(runtime).as_posix() for p in runtime.rglob('*') if p.is_file())==sorted(manifest+['deployment-marker.json'])
 for f in runtime.rglob('*.php'):subprocess.run([str(php),'-n','-l',str(f)],env=env,stdout=subprocess.DEVNULL,check=True)
 # Root config canary proves full original config, including its local include, is used.
 (doc/'includes').mkdir();(doc/'includes/config-local.php').write_text("<?php define('MOCK_LOCAL', 'retained');")
 (doc/'includes/config.php').write_text("<?php require __DIR__.'/config-local.php'; define('MOCK_CONFIG', MOCK_LOCAL.'-root');")
 r=subprocess.check_output([str(php),'-n','-r',f"require '{runtime}/includes/config.php'; echo MOCK_CONFIG;"],env=env,text=True);assert r=='retained-root'
 missing=base/'missing/workday-runtime-v1/includes';missing.mkdir(parents=True);shutil.copyfile(runtime/'includes/config.php',missing/'config.php')
 assert subprocess.run([str(php),'-n',str(missing/'config.php')],env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL).returncode!=0
 # Different timestamps prove new asset cache versions use runtime, not DOCUMENT_ROOT.
 asset='assets/js/app.js';(doc/asset).parent.mkdir(parents=True);(doc/asset).write_text('old');os.utime(doc/asset,(1000000000,1000000000));os.utime(runtime/asset,(1100000000,1100000000))
 r=subprocess.check_output([str(php),'-n','-r',f"$_SERVER['DOCUMENT_ROOT']='{doc}'; require '{runtime}/includes/assets.php'; echo asset_version('/{asset}');"],env=env,text=True);assert r=='1100000000'
 public=[f for f in manifest if not f.startswith('includes/')]+['deployment-marker.json']
 for f in public:
  for root,label in [(doc,'old'),(runtime,'new')]:
   path=root/f;path.parent.mkdir(parents=True,exist_ok=True);path.write_text("<?php echo '"+label+":"+f+"';" if f.endswith('.php') else label+':'+f)
 acme=doc/'.well-known/acme-challenge/mock_token-123';acme.parent.mkdir(parents=True);acme.write_text('mock-acme')
 for f in ['cron/sync-government-calendar.php','database/database.sql','logs/canary.txt','.env','unknown.php']:
  path=doc/f;path.parent.mkdir(parents=True,exist_ok=True);path.write_text('SYNTHETIC_SECRET')
 with socket.socket() as s:s.bind(('127.0.0.1',0));port=s.getsockname()[1]
 conf=base/'httpd.conf';conf.write_text(f'''ServerRoot "{base}"
Listen 127.0.0.1:{port}
ServerName localhost
PidFile "{base}/pid"
ErrorLog "{base}/error.log"
LoadModule mpm_prefork_module "{modules}/mod_mpm_prefork.so"
LoadModule authz_core_module "{modules}/mod_authz_core.so"
LoadModule dir_module "{modules}/mod_dir.so"
LoadModule mime_module "{modules}/mod_mime.so"
LoadModule rewrite_module "{modules}/mod_rewrite.so"
LoadModule php_module "{modules}/libphp8.4.so"
TypesConfig /etc/mime.types
DocumentRoot "{doc}"
<Directory "{doc}">
 AllowOverride All
 Require all granted
</Directory>
<FilesMatch "\\.php$">
 SetHandler application/x-httpd-php
</FilesMatch>
''')
 process=subprocess.Popen([str(apache),'-f',str(conf),'-DFOREGROUND'],env=env,stdout=subprocess.PIPE,stderr=subprocess.PIPE,start_new_session=True)
 def request(path):
  try:
   with urllib.request.urlopen(f'http://127.0.0.1:{port}'+path) as r:return r.status,r.read().decode()
  except urllib.error.HTTPError as e:return e.code,e.read().decode()
 try:
  for i in range(40):
   try:request('/');break
   except urllib.error.URLError:
    if process.poll() is not None:raise RuntimeError(process.stderr.read().decode())
    time.sleep(.05)
  counts=collections.Counter()
  for stage in [0,1,0]:
   (doc/'.htaccess').write_text((repo/'deployment/root-staged.htaccess').read_text().replace('E=WORKDAY_NATIVE_RUNTIME:0','E=WORKDAY_NATIVE_RUNTIME:'+str(stage)))
   for f in public:
    if f=='deployment-marker.json':continue
    status,body=request('/'+f+'?mock=1');assert status==200 and body==('new' if stage else 'old')+':'+f,(stage,f,status,body);counts['exact_public_files']+=1
   for path in ['/','/admin/']:
    status,body=request(path);assert status==200 and body.startswith(('new' if stage else 'old')+':'),(stage,path,status,body);counts['directory_entry_routes']+=1
   for path in ['/includes/config-local.php','/database/database.sql','/cron/sync-government-calendar.php','/logs/canary.txt','/.env','/workday-runtime-v1/login.php','/workday-runtime-v1/assets/js/app.js','/%77orkday-runtime-v1/login.php','//workday-runtime-v1/login.php','/assets/../includes/config-local.php']:
    status,body=request(path);assert status in [403,404] and 'SYNTHETIC_SECRET' not in body,(stage,path,status,body);counts['sensitive_and_direct_runtime_denials']+=1
   if stage==1:
    assert request('/workday-deployment.json')[1]=='new:deployment-marker.json';counts['deployment_marker']+=1
    for path in ['/unknown.php','/api/unknown.php','/login.php/extra']:
     assert request(path)[0] in [403,404];counts['unknown_php_and_path_info']+=1
   # ACME-only exception; no broad .well-known bypass.
   assert request('/.well-known/acme-challenge/mock_token-123')==(200,'mock-acme');counts['acme_token']+=1
   for path in ['/.well-known/other','/.well-known/acme-challenge/mock.php','/.git/config','/assets/.hidden','/.well-known/acme-challenge/']:
    assert request(path)[0] in [403,404];counts['other_dotpath_denials']+=1
   # Echo route proves Apache preserves method, query bytes and body on internal rewrite.
   echo="<?php echo json_encode(['method'=>$_SERVER['REQUEST_METHOD'],'query'=>$_SERVER['QUERY_STRING'],'body'=>file_get_contents('php://input'),'version'=>'VERSION']);"
   for root,label in [(doc,'old'),(runtime,'new')]: (root/'api/auth.php').write_text(echo.replace('VERSION',label))
   for method,body in [('GET',None),('POST',b'{"mock":"body + & Unicode"}'),('PUT',b'mock=1&literal=%2B'),('DELETE',b'mock-delete'),('PATCH',b'mock-patch')]:
    req=urllib.request.Request(f'http://127.0.0.1:{port}/api/auth.php?action=mock&encoded=a%2Bb&empty=',data=body,method=method,headers={'Content-Type':'application/json'})
    with urllib.request.urlopen(req) as r: result=json.loads(r.read())
    assert result=={'method':method,'query':'action=mock&encoded=a%2Bb&empty=','body':(body or b'').decode(),'version':'new' if stage else 'old'},(stage,result)
    counts['query_method_body_preservation']+=1
   for root,label in [(doc,'old'),(runtime,'new')]: (root/'api/auth.php').write_text("<?php echo '"+label+":api/auth.php';")
   # Execute unmodified repository index with synthetic auth, no DB/session implementation.
   for root in [doc,runtime]:
    (root/'index.php').write_text((repo/'index.php').read_text())
    (root/'includes/auth.php').write_text("<?php function is_logged_in(): bool { return ($_GET['mock_logged_in'] ?? '') === '1'; }")
   class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs):return None
   opener=urllib.request.build_opener(NoRedirect())
   for path in ['/','/index.php']:
    for logged,location in [('0','/login.php'),('1','/work-log.php')]:
     try: opener.open(f'http://127.0.0.1:{port}'+path+'?mock_logged_in='+logged);raise AssertionError('Missing redirect')
     except urllib.error.HTTPError as r: assert r.code==302 and r.headers['Location']==location,(stage,path,r.code,r.headers)
     counts['original_index_redirect_with_stub_auth']+=1
   for root,label in [(doc,'old'),(runtime,'new')]: (root/'index.php').write_text("<?php echo '"+label+":index.php';")
  print('PASS PHP lint, full-root config loader, fail-closed config, runtime asset mtime')
  print('Apache checks by category: '+json.dumps(dict(counts),sort_keys=True))
  print('Apache checks total: '+str(sum(counts.values())))
 finally:
  process.terminate();process.wait(timeout=5)

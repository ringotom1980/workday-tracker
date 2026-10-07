"""Local Apache tests with synthetic files only. Never uses production credentials."""
import os,pathlib,tempfile,subprocess,json,time,urllib.request,urllib.error,socket,shutil
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
  count=0
  for stage in [0,1,0]:
   shutil.copyfile(repo/f'deployment/root-stage-{stage}.htaccess',doc/'.htaccess')
   for f in public:
    if f=='deployment-marker.json':continue
    status,body=request('/'+f+'?mock=1');assert status==200 and body==('new' if stage else 'old')+':'+f,(stage,f,status,body);count+=1
   for path in ['/','/admin/']:
    status,body=request(path);assert status==200 and body.startswith(('new' if stage else 'old')+':'),(stage,path,status,body);count+=1
   for path in ['/includes/config-local.php','/database/database.sql','/cron/sync-government-calendar.php','/logs/canary.txt','/.env','/workday-runtime-v1/login.php','/workday-runtime-v1/assets/js/app.js','/%77orkday-runtime-v1/login.php','//workday-runtime-v1/login.php','/assets/../includes/config-local.php']:
    status,body=request(path);assert status in [403,404] and 'SYNTHETIC_SECRET' not in body,(stage,path,status,body);count+=1
   if stage==1:
    assert request('/workday-deployment.json')[1]=='new:deployment-marker.json';count+=1
    for path in ['/unknown.php','/api/unknown.php','/login.php/extra']:
     assert request(path)[0] in [403,404];count+=1
  print(f'PASS PHP lint, full-root config loader, fail-closed config, runtime asset mtime; Apache stage0/stage1/rollback {count} checks')
 finally:
  process.terminate();process.wait(timeout=5)

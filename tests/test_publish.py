import tempfile,pathlib,subprocess,os,json
repo=pathlib.Path(__file__).resolve().parents[1]
def run(*args,cwd=None,ok=True,env=None):
 r=subprocess.run(args,cwd=cwd,env=env,text=True,stdout=subprocess.PIPE,stderr=subprocess.PIPE)
 if ok and r.returncode:raise RuntimeError(r.stderr)
 return r
with tempfile.TemporaryDirectory(prefix='workday-git-') as tmp:
 p=pathlib.Path(tmp);remote=p/'remote.git';source=p/'source';artifact=p/'artifact';artifact.mkdir();(artifact/'index.php').write_text('mock')
 run('git','init','--bare',str(remote));run('git','init',str(source));run('git','config','user.name','Mock',cwd=source);run('git','config','user.email','mock@example.invalid',cwd=source);run('git','checkout','-b','main',cwd=source)
 def source_commit(text):
  (source/'comment').write_text(text);run('git','add','.',cwd=source);run('git','commit','-m',text,cwd=source);run('git','push',str(remote),'main',cwd=source);return run('git','rev-parse','HEAD',cwd=source).stdout.strip()
 sha1=source_commit('first')
 # Only remote URL replaced in temporary harness; production script stays GitHub-only.
 script=p/'publish.sh';script.write_text((repo/'deployment/publish.sh').read_text().replace('remote="https://github.com/${REPOSITORY}.git"','remote="'+str(remote)+'"'))
 env=os.environ.copy();env.update(SOURCE_SHA=sha1,ARTIFACT_DIR=str(artifact),REPOSITORY='ringotom1980/workday-tracker',GH_TOKEN='mock-only')
 # Simulate inherited config file; publisher isolates it with /dev/null.
 config=p/'inherited-config';config.write_text('[http "https://github.com/"]\n extraheader = AUTHORIZATION: duplicate\n');env['GIT_CONFIG_GLOBAL']=str(config)
 run('bash',str(script),env=env);branch='refs/heads/hostinger-workday-runtime-v1';first=run('git','--git-dir',str(remote),'rev-parse',branch).stdout.strip();assert len(run('git','--git-dir',str(remote),'rev-list','--parents','-n','1',first).stdout.split())==1
 sha2=source_commit('second comment');(artifact/'deployment-marker.json').write_text(json.dumps({'source':sha2}));env['SOURCE_SHA']=sha2;run('bash',str(script),env=env);second=run('git','--git-dir',str(remote),'rev-parse',branch).stdout.strip();assert run('git','--git-dir',str(remote),'rev-parse',second+'^').stdout.strip()==first
 env['SOURCE_SHA']=sha1;assert run('bash',str(script),env=env,ok=False).returncode!=0;assert run('git','--git-dir',str(remote),'rev-parse',branch).stdout.strip()==second
 # Git itself rejects a conflicting non-FF update.
 run('git','checkout','--orphan','divergent',cwd=source);run('git','commit','--allow-empty','-m','conflict',cwd=source);assert run('git','push',str(remote),'HEAD:'+branch,cwd=source,ok=False).returncode!=0
 print('PASS real bare Git: first orphan, second FF parent, stale source unchanged, conflicting non-FF rejected; inherited config isolated')

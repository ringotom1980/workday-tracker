#!/usr/bin/env python3
"""Build from tracked source only; output must be empty and outside repository."""
import argparse,json,pathlib,subprocess,shutil,re
p=argparse.ArgumentParser();p.add_argument('output');p.add_argument('--source',required=True);a=p.parse_args()
root=pathlib.Path(__file__).resolve().parents[1];out=pathlib.Path(a.output).resolve()
if out==root or root in out.parents or out.exists(): raise SystemExit('Output must be new and outside repository')
if not re.fullmatch('[0-9a-f]{40}',a.source): raise SystemExit('Invalid source SHA')
manifest=json.loads((root/'deployment/manifest.json').read_text());tracked=set(subprocess.check_output(['git','ls-files'],cwd=root,text=True).splitlines())
if len(manifest)!=len(set(manifest)):raise SystemExit('Duplicate entry')
for name in manifest:
 parts=pathlib.PurePosixPath(name).parts
 if name not in tracked or '..' in parts or name.startswith('/') or any((root.joinpath(*parts[:i])).is_symlink() for i in range(1,len(parts)+1)) or name=='includes/config-local.php':raise SystemExit('Unsafe entry: '+name)
 if not (name.endswith('.php') and parts[0] not in ('cron','database') or parts[0]=='assets' and name.endswith(('.css','.js'))):raise SystemExit('Not allowed: '+name)
for name in manifest:
 dest=out/name;dest.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(root/name,dest)
shutil.copyfile(root/'deployment/runtime-config.php',out/'includes/config.php')
(out/'deployment-marker.json').write_text(json.dumps({'source':a.source,'runtime':'workday-runtime-v1'})+'\n')

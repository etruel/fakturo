# Lists the AJAX and admin-post handlers whose body shows no permission check or no nonce, and the risky patterns
# (redirect to a request value, raw nonce, update_option from $_POST). Adapted from WPeMatico's tool: fktr_guard counts as both checks.
#   python3 tests/audit-handlers.py includes ../fakturo_subscriptions/includes
import re, os, glob, sys, fnmatch

# Handlers checked by hand that the patterns cannot see: pattern -> why it is fine.
REVIEWED = {
    'admin_post_nopriv_fktr_wizard': 'only sends a logged-out visitor to the login page',
    'admin_post_fktr_wizard': 'opens a wizard step after current_user_can(fktr_manage_wizard); its forms carry their own nonce',
    'admin_post_*_download_csv': 'read-only export, reports::access_tab() checks the capability (no nonce by design, see CLAUDE.md)',
    'admin_post_*_print_pdf': 'read-only export, reports::access_tab() checks the capability (no nonce by design, see CLAUDE.md)',
}
def reviewed(hook):
    for pat, why in REVIEWED.items():
        if fnmatch.fnmatch(hook, pat):
            return why
    return None

def body_of(src, fn):
    m = re.search(r'function\s+'+re.escape(fn)+r'\s*\(', src)
    if not m: return ''
    i = src.find('{', m.end())
    if i < 0: return ''
    depth = 0
    for j in range(i, len(src)):
        c = src[j]
        if c == '{': depth += 1
        elif c == '}':
            depth -= 1
            if depth == 0: return src[i:j+1]
    return src[i:]

def scan(d):
    files=[p for p in glob.glob(d+'/**/*.php', recursive=True)
           if not re.search(r'/(lib|libs|vendor|tests)/', p)]
    res={'redir':[], 'nonce_raw':[], 'sincap':[], 'sinnonce':[], 'postdump':[]}
    hooks={}
    for p in files:
        src=open(p,errors='replace').read()
        for m in re.finditer(r'wp_redirect\(\s*\$_(?:POST|GET|REQUEST)\b', src):
            res['redir'].append(f"{p}:{src[:m.start()].count(chr(10))+1}")
        for m in re.finditer(r'wp_verify_nonce\(\s*\$_(?:POST|GET|REQUEST)\[', src):
            if 'isset' not in src[max(0,m.start()-160):m.start()]:
                res['nonce_raw'].append(f"{p}:{src[:m.start()].count(chr(10))+1}")
        for m in re.finditer(r'update_option\(\s*[^,()]+,\s*\$_POST\s*\)', src):
            res['postdump'].append(f"{p}:{src[:m.start()].count(chr(10))+1}")
        for m in re.finditer(r"add_action\(\s*['\"](admin_post_nopriv_|admin_post_|wp_ajax_nopriv_|wp_ajax_|admin_action_)([a-zA-Z0-9_]*)['\"]\s*,\s*(?:array\(\s*[^,]+,\s*)?['\"]?([A-Za-z0-9_]+)", src):
            hooks.setdefault(m.group(1)+m.group(2), (src, m.group(3), p))
    for h,(src,fn,p) in sorted(hooks.items()):
        b = body_of(src, fn)
        if not b: continue
        if reviewed(h):
            res.setdefault('reviewed', []).append(h)
            continue
        if not re.search(r'current_user_can|fktr_guard::(?:ajax|item|can_save|saving)|self::allowed\(|self::check_request\(|::can\(', b):
            res['sincap'].append(h)
        if not re.search(r'check_admin_referer|check_ajax_referer|wp_verify_nonce|self::check_request\(|fktr_guard::(?:ajax|item|can_save|saving)', b):
            res['sinnonce'].append(h)
    return res

if __name__ == '__main__':
    for d in sys.argv[1:]:
        r = scan(d)
        print(f"### {d}")
        for k in ('redir','nonce_raw','postdump','sincap','sinnonce'):
            if r[k]: print(f"   {k:10} ({len(r[k])}): " + ', '.join(r[k][:8]) + (' …' if len(r[k])>8 else ''))
        if r.get('reviewed'): print(f"   reviewed by hand ({len(r['reviewed'])}): " + ', '.join(r['reviewed']))
        if not any(r[k] for k in ('redir','nonce_raw','postdump','sincap','sinnonce')): print("   (nothing to review)")

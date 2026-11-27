@php($unsafe = '"><script>alert(1)</script>')
<x-profile name="Static" :title="$unsafe" />
<x-dynamic-component component="profile" name="Dynamic" :title="$unsafe" />
<x-dynamic-component component="profile" :name="$unsafe" />
<x-dynamic-component component="placeholder" :title="$unsafe" />

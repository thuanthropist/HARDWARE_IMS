<?php

namespace Mrrh\LicenseClient\Services;

/**
 * Generates a fingerprint identifying this installation to the License Server.
 *
 * Recipe: sha256(hostname | machine-id | install path). Each part is chosen so
 * the fingerprint tolerates day-to-day operational changes but still changes on
 * a genuine server migration:
 *
 *  - hostname            — usually stable; if it changes on a routine rename,
 *                           the other two parts keep the fingerprint anchored.
 *  - machine-id           — a hardware/OS-level identifier that survives reboots
 *                           and IP changes, but differs on different hardware:
 *                             Linux:   /etc/machine-id or /var/lib/dbus/machine-id
 *                             Windows: HKLM\SOFTWARE\Microsoft\Cryptography MachineGuid
 *                             Fallback: primary disk serial number
 *  - base_path()          — anchors the fingerprint to *this* installation
 *                           directory, so restoring a backup onto different
 *                           hardware (different machine-id) still produces a
 *                           different fingerprint, while an in-place OS
 *                           reinstall that keeps the same disk/path does not.
 *
 * None of these depend on the current IP address, so a change of network or a
 * reboot never changes the fingerprint. A full server migration (new hardware,
 * new machine-id, new install path) does change it — which is the intended
 * trigger for re-activation.
 */
class ServerFingerprint
{
    public function generate(): string
    {
        $parts = array_filter([
            gethostname() ?: null,
            $this->machineId(),
        ], static fn ($part) => $part !== null && $part !== '');

        if (empty($parts)) {
            // Last resort: at least tie the fingerprint to *some* description
            // of this machine so it isn't empty.
            $parts[] = php_uname();
        }

        $parts[] = base_path();

        return hash('sha256', implode('|', $parts));
    }

    protected function machineId(): ?string
    {
        return $this->linuxMachineId() ?? $this->windowsMachineGuid() ?? $this->diskSerial();
    }

    protected function linuxMachineId(): ?string
    {
        foreach (['/etc/machine-id', '/var/lib/dbus/machine-id'] as $path) {
            if (is_readable($path)) {
                $id = trim((string) @file_get_contents($path));

                if ($id !== '') {
                    return $id;
                }
            }
        }

        return null;
    }

    protected function windowsMachineGuid(): ?string
    {
        if (! $this->isWindows() || ! $this->shellExecAvailable()) {
            return null;
        }

        $output = @shell_exec('reg query "HKLM\SOFTWARE\Microsoft\Cryptography" /v MachineGuid 2>NUL');

        if ($output && preg_match('/MachineGuid\s+REG_SZ\s+([a-f0-9-]+)/i', $output, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function diskSerial(): ?string
    {
        if (! $this->shellExecAvailable()) {
            return null;
        }

        $command = $this->isWindows()
            ? 'wmic diskdrive get serialnumber 2>NUL'
            : '(lsblk -ndo serial 2>/dev/null || blkid -s UUID -o value 2>/dev/null) | head -n1';

        $output = trim((string) @shell_exec($command));

        if ($output === '') {
            return null;
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $output))));

        // `wmic` prints a "SerialNumber" header row before the value.
        $value = $lines[1] ?? $lines[0] ?? null;

        return ($value !== null && $value !== '') ? $value : null;
    }

    protected function isWindows(): bool
    {
        return defined('PHP_WINDOWS_VERSION_MAJOR') || stripos(PHP_OS, 'WIN') === 0;
    }

    protected function shellExecAvailable(): bool
    {
        return function_exists('shell_exec') && ! in_array('shell_exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true);
    }
}

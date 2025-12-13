import 'package:flutter/material.dart';

import '../models/attendance_model.dart';
import '../state/app_state.dart';

class HomeScreen extends StatelessWidget {
  final AppState appState;
  const HomeScreen({super.key, required this.appState});

  @override
  Widget build(BuildContext context) {
    final user = appState.user!;
    final latest = appState.latest;
    final loading = appState.isLoading;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Attendance'),
        actions: [
          IconButton(
            onPressed: loading ? null : () async => appState.refreshAll(),
            icon: const Icon(Icons.refresh),
          ),
          IconButton(
            onPressed: loading
                ? null
                : () async {
                    await appState.logout();
                  },
            icon: const Icon(Icons.logout),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => appState.refreshAll(),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text(
              user.fullName,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
            ),
            Text(user.email, style: const TextStyle(color: Colors.black54)),
            const SizedBox(height: 16),

            _LatestCard(latest: latest),
            const SizedBox(height: 12),

            Row(
              children: [
                Expanded(
                  child: ElevatedButton(
                    onPressed: (loading || appState.hasOpenShift)
                        ? null
                        : () async {
                            try {
                              await appState.clockIn();
                            } catch (_) {
                              if (!context.mounted) return;
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(appState.error ?? 'Clock in failed')),
                              );
                            }
                          },
                    child: Text(loading ? '...' : 'Clock In'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: (loading || !appState.hasOpenShift)
                        ? null
                        : () async {
                            try {
                              await appState.clockOut();
                            } catch (_) {
                              if (!context.mounted) return;
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(appState.error ?? 'Clock out failed')),
                              );
                            }
                          },
                    child: Text(loading ? '...' : 'Clock Out'),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),
            const Text(
              'Recent attendance',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 8),
            if (appState.records.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 24),
                child: Center(child: Text('No attendance records')),
              )
            else
              ...appState.records.map((r) => _AttendanceTile(record: r)),
          ],
        ),
      ),
    );
  }
}

class _LatestCard extends StatelessWidget {
  final AttendanceModel? latest;
  const _LatestCard({required this.latest});

  @override
  Widget build(BuildContext context) {
    if (latest == null) {
      return const Card(
        child: Padding(
          padding: EdgeInsets.all(16),
          child: Text('No attendance yet.'),
        ),
      );
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              latest!.isOpen ? 'Status: CLOCKED IN' : 'Status: CLOCKED OUT',
              style: TextStyle(
                fontWeight: FontWeight.w700,
                color: latest!.isOpen ? Colors.green : Colors.black87,
              ),
            ),
            const SizedBox(height: 8),
            Text('Shift: ${latest!.shiftName ?? '-'}'),
            Text('Clock in: ${latest!.clockInAt}'),
            Text('Clock out: ${latest!.clockOutAt ?? 'OPEN'}'),
            const SizedBox(height: 8),
            Text('Regular: ${AttendanceModel.hoursText(latest!.regularMinutes)} h'),
            Text('Overtime: ${AttendanceModel.hoursText(latest!.overtimeMinutes)} h'),
            Text('Total: ${AttendanceModel.hoursText(latest!.totalMinutes)} h'),
          ],
        ),
      ),
    );
  }
}

class _AttendanceTile extends StatelessWidget {
  final AttendanceModel record;
  const _AttendanceTile({required this.record});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        title: Text(record.shiftName ?? 'Shift'),
        subtitle: Text('${record.clockInAt}  →  ${record.clockOutAt ?? 'OPEN'}'),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text('R ${AttendanceModel.hoursText(record.regularMinutes)}h'),
            Text('OT ${AttendanceModel.hoursText(record.overtimeMinutes)}h'),
          ],
        ),
      ),
    );
  }
}


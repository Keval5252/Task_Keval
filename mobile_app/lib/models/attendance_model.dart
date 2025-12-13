class AttendanceModel {
  final int id;
  final String clockInAt;
  final String? clockOutAt;
  final int totalMinutes;
  final int regularMinutes;
  final int overtimeMinutes;
  final String? shiftName;

  const AttendanceModel({
    required this.id,
    required this.clockInAt,
    required this.clockOutAt,
    required this.totalMinutes,
    required this.regularMinutes,
    required this.overtimeMinutes,
    required this.shiftName,
  });

  factory AttendanceModel.fromJson(Map<String, dynamic> json) {
    final shift = json['shift'];
    return AttendanceModel(
      id: (json['id'] as num).toInt(),
      clockInAt: (json['clock_in_at'] ?? '') as String,
      clockOutAt: json['clock_out_at'] as String?,
      totalMinutes: ((json['total_minutes'] ?? 0) as num).toInt(),
      regularMinutes: ((json['regular_minutes'] ?? 0) as num).toInt(),
      overtimeMinutes: ((json['overtime_minutes'] ?? 0) as num).toInt(),
      shiftName: shift is Map<String, dynamic> ? (shift['name'] as String?) : null,
    );
  }

  bool get isOpen => clockOutAt == null;

  static String hoursText(int minutes) {
    final h = minutes / 60.0;
    return h.toStringAsFixed(2);
  }
}


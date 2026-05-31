
    const JalaliDate = {
        g_days_in_month: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
        j_days_in_month: [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29],

        toJalali: function(gy, gm, gd) {
            let gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
            let gy2 = (gm > 2) ? gy + 1 : gy;
            let days = 355666 + (365 * gy) + ~~((gy2 + 3) / 4) - ~~((gy2 + 99) / 100) + ~~((gy2 + 399) / 400) + gd + gdm[gm - 1];
            let jy = -1595 + (33 * ~~(days / 12053));
            days %= 12053;
            jy += 4 * ~~(days / 1461);
            days %= 1461;
            if (days > 365) {
                jy += ~~((days - 1) / 365);
                days = (days - 1) % 365;
            }
            let jm, jd;
            if (days < 186) {
                jm = 1 + ~~(days / 31);
                jd = 1 + (days % 31);
            } else {
                jm = 7 + ~~((days - 186) / 30);
                jd = 1 + ((days - 186) % 30);
            }
            return [jy, jm, jd];
        },

        toGregorian: function(jy, jm, jd) {
            jy += 1595;
            let days = -355668 + 365 * jy + ~~(jy / 33) * 8 + ~~(((jy % 33) + 3) / 4) + jd;
            days += (jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186;
            let gy = 400 * ~~(days / 146097);
            days %= 146097;
            if (days > 36524) {
                gy += 100 * ~~(--days / 36524);
                days %= 36524;
                if (days >= 365) days++;
            }
            gy += 4 * ~~(days / 1461);
            days %= 1461;
            if (days > 365) {
                gy += ~~((days - 1) / 365);
                days = (days - 1) % 365;
            }
            let gd = days + 1;
            let sal_a = [0, 31, ((gy % 4 == 0 && gy % 100 != 0) || (gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            let gm;
            for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
            return [gy, gm, gd];
        },

        today: function() {
            let d = new Date();
            return this.toJalali(d.getFullYear(), d.getMonth() + 1, d.getDate());
        },

        monthName: function(m) {
            return ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'][m - 1];
        },

        daysInMonth: function(jy, jm) {
            if (jm <= 6) return 31;
            if (jm < 12) return 30;
            let g = this.toGregorian(jy, 12, 29);
            let nextG = this.toGregorian(jy, 12, 30);
            return (g[0] !== nextG[0]) ? 29 : 30;
        }
    };

    class CalendarPicker {
        constructor(wrapperId, calendarId, inputId, clearOnSelect = false) {
            this.wrapper = document.getElementById(wrapperId);
            this.calendar = document.getElementById(calendarId);
            this.input = document.getElementById(inputId);
            this.currentDate = JalaliDate.today();
            this.selectedDate = null;
            this.clearOnSelect = clearOnSelect;

            this.buildCalendar();
            this.addEvents();
        }

        buildCalendar() {
            let [jy, jm, jd] = this.currentDate;
            let today = JalaliDate.today();
            let daysInMonth = JalaliDate.daysInMonth(jy, jm);

            let firstDayG = JalaliDate.toGregorian(jy, jm, 1);
            let firstDayDate = new Date(firstDayG[0], firstDayG[1] - 1, firstDayG[2]);
            let firstDayOfWeek = firstDayDate.getDay();
            let persianFirstDay = (firstDayOfWeek + 1) % 7;

            let html = '';

            html += '<div class="calendar-header">';
            html += `<button class="calendar-nav prev-month" type="button"><svg viewBox="0 0 24 24" style="fill:none;stroke:currentColor;stroke-width:2;"><polyline points="15 18 9 12 15 6"/></svg></button>`;
            html += `<span class="calendar-month-year">${JalaliDate.monthName(jm)} ${jy}</span>`;
            html += `<button class="calendar-nav next-month" type="button"><svg viewBox="0 0 24 24" style="fill:none;stroke:currentColor;stroke-width:2;"><polyline points="9 18 15 12 9 6"/></svg></button>`;
            html += '</div>';

            html += '<div class="calendar-weekdays">';
            ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'].forEach(d => {
                html += `<span>${d}</span>`;
            });
            html += '</div>';

            html += '<div class="calendar-days">';

            for (let i = 0; i < persianFirstDay; i++) {
                html += '<span class="calendar-day empty"></span>';
            }

            for (let d = 1; d <= daysInMonth; d++) {
                let classes = 'calendar-day';
                if (jy === today[0] && jm === today[1] && d === today[2]) classes += ' today';
                if (this.selectedDate && this.selectedDate[0] === jy && this.selectedDate[1] === jm && this.selectedDate[2] === d) classes += ' selected';
                html += `<button type="button" class="${classes}" data-day="${d}">${this.toPersianNum(d)}</button>`;
            }

            html += '</div>';

            html += '<div class="calendar-footer">';
            html += '<button type="button" class="btn-clear">پاک کردن</button>';
            html += '<button type="button" class="btn-today">امروز</button>';
            html += '</div>';

            this.calendar.innerHTML = html;
            this.currentMonthYear = {
                jy,
                jm,
                jd
            };
        }

        toPersianNum(n) {
            let persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return n.toString().replace(/\d/g, d => persian[d]);
        }

        addEvents() {
            this.input.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.jalali-calendar.active').forEach(cal => {
                    if (cal !== this.calendar) cal.classList.remove('active');
                });
                this.calendar.classList.toggle('active');
            });

            this.calendar.addEventListener('click', (e) => {
                let dayBtn = e.target.closest('.calendar-day');
                if (dayBtn && !dayBtn.classList.contains('empty')) {
                    let day = parseInt(dayBtn.dataset.day);
                    let {
                        jy,
                        jm
                    } = this.currentMonthYear;
                    this.selectedDate = [jy, jm, day];
                    let formatted = `${jy}/${String(jm).padStart(2,'0')}/${String(day).padStart(2,'0')}`;
                    this.input.value = formatted;
                    this.calendar.classList.remove('active');
                    this.buildCalendar();
                }

                if (e.target.closest('.prev-month')) {
                    e.preventDefault();
                    this.changeMonth(-1);
                }
                if (e.target.closest('.next-month')) {
                    e.preventDefault();
                    this.changeMonth(1);
                }

                if (e.target.closest('.btn-clear')) {
                    e.preventDefault();
                    this.selectedDate = null;
                    this.input.value = '';
                    this.calendar.classList.remove('active');
                    this.buildCalendar();
                }

                if (e.target.closest('.btn-today')) {
                    e.preventDefault();
                    let today = JalaliDate.today();
                    this.selectedDate = today;
                    this.input.value = `${today[0]}/${String(today[1]).padStart(2,'0')}/${String(today[2]).padStart(2,'0')}`;
                    this.calendar.classList.remove('active');
                    this.buildCalendar();
                }
            });

            document.addEventListener('click', (e) => {
                if (!this.wrapper.contains(e.target)) {
                    this.calendar.classList.remove('active');
                }
            });
        }

        changeMonth(delta) {
            let {
                jy,
                jm
            } = this.currentMonthYear;
            jm += delta;
            if (jm > 12) {
                jm = 1;
                jy++;
            }
            if (jm < 1) {
                jm = 12;
                jy--;
            }
            this.currentDate = [jy, jm, 1];
            this.buildCalendar();
        }
    }

    let calFrom = new CalendarPicker('dateFromWrapper', 'calendarFrom', 'dateFrom');
    let calTo = new CalendarPicker('dateToWrapper', 'calendarTo', 'dateTo');

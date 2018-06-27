/*---------------------------------------------------------------------
 * File:  TIM_bridge.c
 *
 * External Entity:  Time (TIM)
 *                            
 * Description:
 * This file provides an implementation of the standard Shlaer-Mellor
 * timer functionality.  Usage is conformed to the bridge interface
 * as described in the BridgePoint Action Language Users Guide.
 *
 * Modify this file to match project design requirements.  Simply add
 * or remove code.  Special comments draw attention to where
 * modifications can most easily be made.
 *
 * Only a subset of the TIM interfaces are provided in this simple
 * prototype implementation.  Recurring timers and timer expiration
 * modification are not supported.  Long integers are used to store
 * time values thus limiting the duration of timers and the system
 * ticker to about 71 minutes.  The sample implementation uses
 * the localtime, mktime, ftime and time library routines.
 *
 * For this example implementation to work, TIM_init() must be
 * invoked at start-up (perhaps from UserInitializationCallout).
 * Also, TIM_tick() must be invoked periodically and as often
 * as practical (perhaps from UserBackgroundProcessingCallout).
 * The resolution of the timers is driven largely by the frequency
 * of invocation of TIM_tick().
 *
 * Notice:
 * (C) Copyright 1999 ROX Software, Inc.
 *     All rights reserved.
 *-------------------------------------------------------------------*/

#include <sys/timeb.h>
#include <time.h>
#include "TIM_bridge.h"
#include "sys_init.h"

typedef unsigned long ETimer_time_t;

/*---------------------------------------------------------------------
 * Timer "Object" Structure Declaration
 *    [next] is the mechanism used to collect and sequence timers.
 *    Timer instances are strung together in an active (animate)
 *    list and an inactive (inanimate) list.  The next pointer
 *    provides the "hole for the beads".
 *    [expiration] is the system clock time at which this
 *    timer will pop.
 *    [t0] (not used) is the system time at installation and
 *    would be useful with recurring timers.
 *    [event] is the handle of the event that the timer will
 *    generate upon expiration.
 *    [flags] (not used) provides boolean variables useful to
 *    indicate whether the timer has popped or whether it is
 *    a recurring timer.
 *-------------------------------------------------------------------*/
typedef struct ETimer_s ETimer_t;
struct ETimer_s {
  ETimer_t * next;
  ETimer_time_t expiration;
  OoaEvent_t * event;
};

static ETimer_t swtimers[ SYS_MAX_OOA_TIMERS ];  /* system.clr color */
static ETimer_t * animate = (ETimer_t *) 0, * inanimate = (ETimer_t *) 0;
static ETimer_time_t tinit = 0;
static struct timeb systyme;

static void timer_insert_sorted( ETimer_t * );
static void timer_fire( ETimer_t * const );
static ETimer_time_t ETimer_msec_time( void );
static ETimer_t *timer_start( const ETimer_time_t, OoaEvent_t * const );
static bool timer_cancel( ETimer_t * );


/*=====================================================================
 * BridgePoint Primitive:
 * <timer_inst_ref_var> = TIM::timer_start(
 *   microseconds:<integer_var>,
 *   event_inst:<event_inst_var> )
 * This bridge starts up an instance of a one-shot S-M OOA timer.
 *===================================================================*/
Escher_Timer_t *
TIM_timer_start(
    Escher_OoaEvent_s * ee_event_inst,
    const Escher_uSec_t ee_microseconds )
{
  /* Replace with implementation specific code here.  */
  return (Escher_Timer_t *) timer_start( ee_microseconds/1000, ee_event_inst );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <timer_inst_ref_var> = TIM::timer_start_recurring(
 *   microseconds:<integer_var>,
 *   event_inst:<event_inst_var> )
 * This bridge starts up an instance of a recurring S-M OOA timer.
 *===================================================================*/
Escher_Timer_t *
TIM_timer_start_recurring(
    Escher_OoaEvent_s * ee_event_inst,
    const Escher_uSec_t ee_microseconds )
{
  /* Insert implementation specific code here.  */
  Escher_Timer_t * result = (Escher_Timer_t *) 0; /* not implemented */
  return result;
}

/*=====================================================================
 * BridgePoint Primitive:
 * <integer_var> = TIM::timer_remaining_time(
 *   timer_inst_ref:<timer_inst_ref_var> )
 * Return the remaining time of the specified timer.
 *===================================================================*/
Escher_uSec_t
TIM_timer_remaining_time(
    const Escher_Timer_t * const ee_timer_inst_ref )
{
  /* Replace with implementation specific code here.  */
  return ( ee_timer_inst_ref == (Escher_Timer_t *) 0 ) ? 0 :
	  ( 1000 * ((ETimer_t *) ee_timer_inst_ref)->expiration );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <was_running_flag> = TIM_timer_reset_time(
 *   microseconds:<integer_var>,
 *   timer_inst_ref:<timer_inst_ref_var> )
 * Try to change expiration of an existing timer.
 * If successful, return true.
 *===================================================================*/
bool
TIM_timer_reset_time(
    const Escher_uSec_t ee_microseconds,
    Escher_Timer_t * const ee_timer_inst_ref )
{
  /* Insert implementation specific code here.  */
  return ( false );             /* not implemented */
}

/*=====================================================================
 * BridgePoint Primitive:
 * <was_running_flag> = TIM_timer_add_time(
 *   microseconds:<integer_var>,
 *   timer_inst_ref:<timer_inst_ref_var> )
 * Try to add time to an existing timer.
 * If successful, return true.
 *===================================================================*/
bool
TIM_timer_add_time(
    const Escher_uSec_t ee_microseconds,
    Escher_Timer_t * const ee_timer_inst_ref )
{
  /* Insert implementation specific code here.  */
  return ( false );             /* not implemented */
}

/*=====================================================================
 * BridgePoint Primitive:
 * <was_running_flag> = TIM::timer_cancel(
 *   timer_inst_ref:<timer_inst_ref_var> )
 * This attempts to cancel the specified timer.
 * Return true if we actually cancelled the timer.
 *===================================================================*/
bool
TIM_timer_cancel(
    Escher_Timer_t * const ee_timer_inst_ref )
{
  /* Replace with implementation specific code here.  */
  return timer_cancel( (ETimer_t *) ee_timer_inst_ref );
}


/*=====================================================================
 * BridgePoint Primitive:
 * <date_var> = TIM::current_date()
 * Return a variable representing the current calendar date.
 *===================================================================*/
Escher_Date_t
TIM_current_date()
{
  /* Replace with implementation specific code here.  */
  return ( (Escher_Date_t) time( 0 ) );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <date_var> = TIM::create_date(
 *   day:<integer_var>,
 *   hour:<integer_var>,
 *   minute:<integer_var>,
 *   month:<integer_var>,
 *   second:<integer_var>,
 *   year:<integer_var> )
 * Return a variable representing the calendar date as specified
 * by the input components.
 *===================================================================*/
Escher_Date_t
TIM_create_date(
    const int ee_day,
    const int ee_hour,
    const int ee_minute,
    const int ee_month,
    const int ee_second,
    const int ee_year )
{
  /* Replace with implementation specific code here.  */
  struct tm t;
  t.tm_mday = ee_day;
  t.tm_hour = ee_hour;
  t.tm_min = ee_minute;
  t.tm_mon = ee_month;
  t.tm_sec = ee_second;
  t.tm_year = ee_year;
  t.tm_year = ee_year;
  return ( (Escher_Date_t) mktime( &t ) );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <integer_var> = TIM::get_second(
 *   date:<integer_var> )
 * Return the year field of the date variable.
 *===================================================================*/
int
TIM_get_second(
    const Escher_Date_t ee_date )
{
  /* Replace with implementation specific code here.  */
  struct tm * tp;
  tp = localtime( &ee_date );
  return ( tp->tm_sec );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <integer_var> = TIM::get_minute(
 *   date:<integer_var> )
 * Return the year field of the date variable.
 *===================================================================*/
int
TIM_get_minute(
    const Escher_Date_t ee_date )
{
  /* Replace with implementation specific code here.  */
  struct tm * tp;
  tp = localtime( &ee_date );
  return ( tp->tm_min );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <integer_var> = TIM::get_hour(
 *   date:<integer_var> )
 * Return the year field of the date variable.
 *===================================================================*/
int
TIM_get_hour(
    const Escher_Date_t ee_date )
{
  /* Replace with implementation specific code here.  */
  struct tm * tp;
  tp = localtime( &ee_date );
  return ( tp->tm_hour );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <integer_var> = TIM::get_day(
 *   date:<integer_var> )
 * Return the year field of the date variable.
 *===================================================================*/
int
TIM_get_day(
    const Escher_Date_t ee_date )
{
  /* Replace with implementation specific code here.  */
  struct tm * tp;
  tp = localtime( &ee_date );
  return ( tp->tm_mday );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <integer_var> = TIM::get_month(
 *   date:<integer_var> )
 * Return the year field of the date variable.
 *===================================================================*/
int
TIM_get_month(
    const Escher_Date_t ee_date )
{
  /* Replace with implementation specific code here.  */
  struct tm * tp;
  tp = localtime( &ee_date );
  return ( tp->tm_mon );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <integer_var> = TIM::get_year(
 *   date:<integer_var> )
 * Return the year field of the date variable.
 *===================================================================*/
int
TIM_get_year(
    const Escher_Date_t ee_date )
{
  /* Replace with implementation specific code here.  */
  struct tm * tp;
  tp = localtime( &ee_date );
  return ( tp->tm_year );
}

/*=====================================================================
 * BridgePoint Primitive:
 * <timestamp_var> = TIM::current_clock()
 * This bridge returns a system dependent time value.
 *===================================================================*/
Escher_TimeStamp_t
TIM_current_clock()
{
  /* Replace with implementation specific code here.  */
  return ( ETimer_msec_time() );
}


/* Routines below are implementation specific.  Modify to taste.     */


/*---------------------------------------------------------------------
 * Get a timer instance from the inanimate list, provide the
 * expiration time and insert it into its proper location among
 * the currently ticking timers.
 *-------------------------------------------------------------------*/
static ETimer_t *timer_start(
  const ETimer_time_t duration,
  OoaEvent_t * const event
)
{
  ETimer_t * t = inanimate;
	if ( t == (ETimer_t *) 0 ) return (ETimer_t *) 0;
  inanimate = inanimate->next;
  t->event = event;
  /*-----------------------------------------------------------------*/
  /* Calculate the timer expiration time.                            */
  /* Note:  Add one to the duration to make sure that delay is       */
  /* at least as long as duration.                                   */
  /*-----------------------------------------------------------------*/
  t->expiration = ETimer_msec_time() + duration + 1;
  timer_insert_sorted( t );
  return ( t );
}

/*---------------------------------------------------------------------
 * Insert a timer into the list of ticking timers maintaining
 * an order that fires timers chronologically.
 *-------------------------------------------------------------------*/
static void timer_insert_sorted(
  ETimer_t * t
)
{
  ETimer_time_t poptime = t->expiration;
  if ( animate == (ETimer_t *) 0 ) {                  /* empty list   */
    t->next = (ETimer_t *) 0;
    animate = t;
  } else if ( poptime <= animate->expiration ) {     /* before head  */
    t->next = animate;
    animate = t;         
  } else {                                           /* find bigger  */
    ETimer_t * prev = animate;
    ETimer_t * cursor;
    while ( ( cursor = prev->next ) != (ETimer_t *) 0 ) {
      if ( poptime <= cursor->expiration ) {
        break;
      }
      prev = cursor;
    }
    prev->next = t;                                  /* link in      */
    t->next = cursor;
  }
}


/*---------------------------------------------------------------------
 * Insert a timer into the list of ticking timers maintaining
 * an order that fires timers chronologically.
 *-------------------------------------------------------------------*/
static bool timer_cancel(
  ETimer_t * t
)
{
  if ( t == (ETimer_t *) 0 ) {
    return ( false );
  }
  if ( animate == (ETimer_t *) 0 ) {
    return ( false );
  }
  /*-----------------------------------------------------------------*/
  /* Check to see if the timer has already been reset.  This check   */
  /* is probabilistic.  It could have a hole if multitasked.         */
  /* We need to try to unlink and see if we actually unlinked.       */
  /* Attempt to remove the timer from the list of running timers.    */
  /* If we succeed, then we can cancel/delete the timer.  If the     */
  /* timer is not in the list, then there is no point in attempting  */
  /* to do any more.                                                 */
  /*-----------------------------------------------------------------*/
  if ( t == animate ) {
    animate = animate->next;
  } else {
    ETimer_t * prev = animate;
    ETimer_t * cursor;
    while ( ( cursor = prev->next ) != t ) {           /* find */
      if ( cursor == (ETimer_t *) 0 ) {
        return ( false );
      }
      prev = cursor;
    }
    prev->next = t->next;                     /* unlink */
  }
  t->next = inanimate;
  inanimate = t;
  return ( true );
}


/*---------------------------------------------------------------------
 * Generate delayed event to the application.
 * Deactivate fired timer.
 *-------------------------------------------------------------------*/
static void timer_fire(
  ETimer_t * const t
)
{
  Escher_SendEvent( t->event );
	t->event = (OoaEvent_t *) 0;
  animate = animate->next;      /* Remove from active list.          */
  t->next = inanimate;          /* Connect to inactive list.         */
  inanimate = t;
}

/*---------------------------------------------------------------------
 * This is the low level mechanism for monotonically increasing
 * at a constant rate.  Substitute code here to read some
 * sort of hardware timer.
 *-------------------------------------------------------------------*/
static ETimer_time_t ETimer_msec_time()
{
  ETimer_time_t t;
  ftime( &systyme );
  t = ( systyme.time * 1000 ) + systyme.millitm;
  return ( t - tinit );
}

/*---------------------------------------------------------------------
 * This is the repetitively invoked timer poller.
 * This routine needs to be run periodically.       
 *-------------------------------------------------------------------*/
void TIM_tick()
{
  /*-----------------------------------------------------------------*/
  /* Check to see if there are timers in the ticking timers list.    */
  /*-----------------------------------------------------------------*/
  if ( animate != (ETimer_t *) 0 ) {
    if ( animate->expiration <= ETimer_msec_time() ) {
      timer_fire( animate );
    }
  }
}

/*---------------------------------------------------------------------
 * Initialize the tick mechanism and the timer instances.
 *-------------------------------------------------------------------*/
void TIM_init()
{
  int i;
  ftime( &systyme );            /* Initialize the hardware ticker.   */
  tinit = ( systyme.time * 1000 ) + systyme.millitm;
  /*-----------------------------------------------------------------*/
  /* Build the collection (linked list) of timers.                   */
  /*-----------------------------------------------------------------*/
  for ( i = 0; i < SYS_MAX_OOA_TIMERS; i++ ) {
    swtimers[ i ].expiration = 0;
    swtimers[ i ].event = (OoaEvent_t *) 0;
    swtimers[ i ].next = inanimate;
    inanimate = &swtimers[ i ];
  }
}

//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2009 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
var loaded = 0;
(function($_){$_.extend($_, {
    sound:{
        e:{},
        sounds:[],
        thisSound: null,
        emptyTime: '-:--',
        isReady:false,
        updatePlayng:'index.php?module=audio&do=update_playng',

        init:function(){
            $_.include.script('includes/javascript/soundmanager/soundmanager.js', function(){
                var defaultOptions = {autoLoad:false, autoPlay:false, multiShot:true, debugMode:false, consoleOnly:false}
                for (var i in defaultOptions) soundManager[i] = defaultOptions[i];
                soundManager.url = 'includes/javascript/soundmanager/';
                setTimeout(function(){soundManager.onready(function(){KR_AJAX.sound.isReady = true;});}, 100);
            });            
        },
        
        creacte_audio:function(e){
            if(KR_AJAX.sound.isReady == true){
                for(var i in e) {soundManager.createSound({
                    id:i,
                    url:'uploads/audio/record/'+e[i],
                    onplay:$_.sound.event.play,
                    onstop:$_.sound.event.stop,
                    onpause:$_.sound.event.pause,
                    onresume:$_.sound.event.resume,
                    onfinish:$_.sound.event.finish,
                    whileloading:$_.sound.event.whileloading,
                    whileplaying:$_.sound.event.whileplaying,
                    onmetadata:$_.sound.event.metadata,
                    onload:$_.sound.event.onload
                });}                 
            } else setTimeout(function(){KR_AJAX.sound.creacte_audio(e)}, 100);
        },

        addAudio:function(e){
            for(var i in e) {$_.sound.e[i] = {}; $_.sound.sounds[$_.sound.sounds.length] = i;}            
            $.krReady(function(){KR_AJAX.sound.creacte_audio(e)});
        },

        play:function(e){
            if(KR_AJAX.sound.isReady==true){                
                this.stopAll(e);
                if(this.thisSound!=e) {
                    haja({action:$_.sound.updatePlayng, animation:false}, {'file':e}, {});
                    $$('time_'+e).innerHTML = this.emptyTime+' / '+this.emptyTime;
                }
                $$('track_'+e).innerHTML = $_.sound.getTrackLine(e);
                soundManager.play(this.thisSound = e);
            } else setTimeout(function(){KR_AJAX.sound.play(e)}, 100);
        },

        stop:function(e){soundManager.stop(e);},
        pause:function(e){soundManager.pause(e);},
        stopAll:function(e){for (var i in $_.sound.e) {(e!=i)?this.stop(i):this.pause(i); $$('play_button_'+i).className = 'play'; if(e!=i){ $$('time_'+i).innerHTML = ''; $$('track_'+i).innerHTML = '<div class="lineDod"></div>';}}},
        getTrackLine:function(e){return "<div class='setPosition'><div class='trackLine'><div class='trackLoading' style='width: "+loaded+"%;'><div class='trackPosition' style='width:"+(($_.sound.e[e].position*100/$_.sound.e[e].duration).toFixed(0))+"%;'></div></div></div></div>";},
        
        getTime:function(msec){
            var ms = Math.floor(msec/1000);
            var s = ms-(Math.floor(ms/60)*60);
            return Math.floor(ms/60)+':'+(s<10?'0'+s:s);
        },

        playTrack:function(e, button){
            if(button.className=='play'){$_.sound.play(e); button.className='pause';} else {$_.sound.pause(e); button.className='play';}
            button.onclick = function(){$_.sound.playTrack(e, button, button.className=='play'?'pause':'play');}
        },

        createPlayList:function(e){            
            var html = "<table width='100%' class='sound' cellspacing='3' cellpadding='3'>";
            for (var i in $_.sound.e) if($$(i)) html += "<tr><td rowspan='2' width='30' align='center'><img id='play_button_"+i+"' onclick=\"KR_AJAX.sound.playTrack('"+i+"', this);\" src='includes/images/pixel.gif' alt='' class='play' /></td><td>"+(($$(i)) ? $$(i).innerHTML : 'undefined')+"</td><td id='time_"+i+"' align='right' width='100' class='timeTrack'></td></tr><tr><td id='track_"+i+"' colspan='2'><div class='lineDod'></div></td></tr>";
            $$(e).innerHTML = html+"</table>";
        },

        nextPlay:function(e){
            if($_.sound.sounds.length>1){
                var nP, n = false, j=0;
                for (var i in $_.sound.e){
                    if(j==0 || n==true) nP = i;
                    n = (i==e && n!=true)?true:false;
                    j++;
                }
                this.playTrack(nP, $$('play_button_'+nP));
            }
        },

        event:{
            play:null,
            stop:null,
            pause:null,
            finish:function(){$_.sound.nextPlay(this.sID);},
            whileloading:function(){loaded = (this.bytesLoaded*100/this.bytesTotal).toFixed(0);},
            whileplaying:function(){
                $_.sound.e[this.sID] = {position: this.position, duration: this.durationEstimate, leftTime: $_.sound.getTime(this.position), totalTime: $_.sound.getTime(this.durationEstimate)};
                $$('time_'+this.sID).innerHTML = $_.sound.e[this.sID].leftTime+' / '+$_.sound.e[this.sID].totalTime;
                $$('track_'+this.sID).innerHTML = $_.sound.getTrackLine(this.sID);
            },
            metadata:null,
            onload:null
        }
    }
})
})(KR_AJAX)
KR_AJAX.sound.init();
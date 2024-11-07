<x-chat-layout>
<div class="flex flex-col items-center justify-center bg-gray--400 w-full h-screen">
    <div class="w-full @if($content) h-full @else h-2/3  @endif lg:w-2/3  bg-gray--500 justify-center flex flex-col">

       
        <div class="w-1/3 self-left text-center text-red-500 font-sans text-[60px] lg:text-[60px]">
            lollol
        </div>
        <div class="w-1/3 -mt-5 self-left text-center text-red-500 font-sans text-[10px] lg:text-[10px]">
            
        </div>

        <style>
            p {
                margin-top: 0.75rem;
            }
        </style>
        <div class="w-full h-[80%] pr-5 overflow-auto flex flex-col-reverse gap-4">
            
            <div class='w-full md:w-4/5 lg:w-2/3 px-5 pb-4 pt-1 ml-3 mb-5  border rounded-xl p-3 shadow-lg' wire:stream="response">
                <div class="w-10 pt-[0.75rem]"><svg viewBox="0 0 50 20">
                        <g>
                            <circle id="dot1" cx="10" cy="10" r="5" fill="#aaa"></circle>
                            <circle id="dot2" cx="25" cy="10" r="5" fill="#aaa"></circle>
                            <circle id="dot3" cx="40" cy="10" r="5" fill="#aaa"></circle>
                        </g>

                        <style>
                            #dot1,
                            #dot2,
                            #dot3 {
                                animation: pulse 2s infinite;
                            }

                            @keyframes pulse {
                                0% {
                                    opacity: 1;
                                }

                                50% {
                                    opacity: 0.3;
                                }

                                100% {
                                    opacity: 1;
                                }
                            }

                            #dot2 {
                                animation-delay: 500ms;
                            }

                            #dot3 {
                                animation-delay: 1s;
                            }
                        </style>
                    </svg>
                </div>

            </div>
           
            {!! $content !!}

        </div>
       

        <div class="w-full self-center text-center text-red-500 leading-none font-sans text-[160px] md:text-[250px] lg:text-[300px]">
            lollol
        </div>
        <div class="w-full self-center text-center text-red-500 font-sans text-[18px] leading-none text-[10px] md:text-[15px] lg:text-[20px] -mt-2 md:-mt-5">
            
        </div>
      

        <div class="w-full px-3 md:px-10 md:w-1/2 sm:w-[400px] md:w-[450px] lg:w-[500px] mx-auto mt-[80px]">
            <form wire:submit.prevent="sendPrompt" class="flex flex-row">
                <input type="text" id="prompt" wire:model="prompt" class="w-full rounded-l-xl focus:border-gray-500 focus:ring-0" autofocus />
                <button class="flex items-center justify-center bg-red-500 hover:bg-red-600 rounded-r-xl text-white px-4 py-1 flex-shrink-0" type="submit">
                    <span>Ask</span>
                    <span class="ml-2">
                        <svg class="w-4 h-4 transform rotate-45 -mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </span>
                </button>
            </form>

            <div class="w-full self-center text-center text-red-500 font-sans text-sm my-5">
                
                <button wire:click="startOver" class="mr-10 mx-3 ">Start over</button>
               
                <a href="{{ route('chat.privacy') }}">Privacy Policy</button>
            </div>
        </div>

       

    </div>
                            


</div>
</x-chat-layout>
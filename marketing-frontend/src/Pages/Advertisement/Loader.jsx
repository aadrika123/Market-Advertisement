import React, { useState } from 'react'
import { ColorRing, MutatingDots, Dna, CirclesWithBar, Oval, RotatingLines, Blocks } from 'react-loader-spinner';

function Loader(props) {
    // console.log('props loader ', props.show)
    return (
        <>
            <div className={`${props.show ? 'grid' : 'hidden'} bg-gray-50 opacity-50  z-50 absolute w-full h-full`}>
            <div className=" mx-auto my-[20%] ">

                <RotatingLines
                    visible={props.show}
                    strokeColor="gray"
                    strokeWidth="3"
                    animationDuration="0.80"
                    width="80"
               
                

                />
                {/* <Oval
                visible={props.show}
                height="60"
                width="90"
                color="#666"
                wrapperStyle={{}}
                wrapperClass=""
                // visible={true}
                ariaLabel='oval-loading'
                secondaryColor="#666"
                strokeWidth="2"
                strokeWidthSecondary="1"

            /> */}
                {/* <ColorRing
                visible={props.show}
                height="80"
                width="80"
                ariaLabel="blocks-loading"
                wrapperStyle={{}}
                wrapperClass="blocks-wrapper"
                colors={['#e15b64', '#f47e60', '#f8b26a', '#abbd81', '#849b87']}
            /> */}

</div>
            </div>
        </>
    )
}

export default Loader
#include "RoadNode.h"
#include "RoadSegment.h"
#include "Components/SceneComponent.h"

ARoadNode::ARoadNode()
{
    SceneComponent = CreateDefaultSubobject<USceneComponent>(TEXT("Root"));
    RootComponent = SceneComponent;
}

void ARoadNode::AddConnectedSegment(ARoadSegment* Segment)
{
    if (!Segment)
    {
        return;
    }
    ConnectedSegments.AddUnique(Segment);
}

void ARoadNode::RemoveConnectedSegment(ARoadSegment* Segment)
{
    ConnectedSegments.Remove(Segment);
}

const TArray<TWeakObjectPtr<ARoadSegment>>& ARoadNode::GetConnectedSegments() const
{
    return ConnectedSegments;
}

void ARoadNode::EndPlay(const EEndPlayReason::Type EndPlayReason)
{
    for (TWeakObjectPtr<ARoadSegment>& Segment : ConnectedSegments)
    {
        if (Segment.IsValid())
        {
            Segment->Destroy();
        }
    }
    ConnectedSegments.Reset();
    Super::EndPlay(EndPlayReason);
}
